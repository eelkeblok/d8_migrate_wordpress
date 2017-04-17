<?php

/**
 * @file
 * Contains \Drupal\migrate_wordpress\Plugin\migrate\source\Posts.
 */

namespace Drupal\migrate_wordpress\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
use Drupal\Core\Database\Query\Condition;
use Drupal\migrate_wordpress\WpOptionTrait;
use Drupal\bootstrap\Utility\Unicode;

/**
 * Extract posts from Wordpress database.
 *
 * @MigrateSource(
 *   id = "posts"
 * )
 */
class Posts extends DrupalSqlBase {
  use WpOptionTrait;

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Select posts and pages.
    $postTypeCondition = new Condition('OR');
    $postTypeCondition->condition('post_type', 'post')->condition('post_type', 'page');
    $query = $this->select('posts', 'p')
      ->fields('p', array_keys($this->postFields()))
      ->condition($postTypeCondition);

    return $query;
  }

  /**
   * Returns the Posts fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function postFields() {
    $fields = array(
      'id' => $this->t('Post ID'),
      'post_title' => $this->t('Title'),
      'post_content' => $this->t('Content'),
      'post_author' => $this->t('Authored by (uid)'),
      'post_type' => $this->t('Post type'),
      'post_date' => $this->t('Post date'),
      'post_modified' => $this->t('Post modified date'),
      'post_status' => $this->t('Post status'),
      'post_name' => $this->t('Post machine name'),
    );
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->postFields();
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $post_type = $row->getSourceProperty('post_type');
    $type = $post_type == 'page' ? 'page' : 'article';
    $row->setSourceProperty('type', $type);

    // Build a path alias.
    $permalink_structure = $this->getOption('permalink_structure');

    // If the last character of the pattern is a slash, strip it off.
    $length = Unicode::strlen($permalink_structure);
    if (Unicode::substr($permalink_structure, $length - 1, 1) == '/') {
      $permalink_structure = Unicode::substr($permalink_structure, 0, $length - 1);
    }

    $post_date = $row->getSourceProperty('post_date');
    $post_name = $row->getSourceProperty('post_name');

    if (!empty($post_date) && !empty($post_name)) {
      $post_time = strtotime($post_date);

      $alias = $permalink_structure;

      $replacements = [
        '%postname%' => $post_name,
        '%year%' => date('Y', $post_time),
        '%monthnum%' => date('m', $post_time),
        '%day%' => date('d', $post_time),
      ];

      foreach ($replacements as $needle => $replacement) {
        $alias = str_replace($needle, $replacement, $alias);
      }

      // Only set the source property if the alias does not contain any
      // placeholders anymore.
      if (strpos($alias, '%') === FALSE) {
        $row->setSourceProperty('alias', $alias);
      }
    }

    // Pull in tags and categories.
    // Array holding name of source property as the key and the WP vocabulary
    // name as the value.
    $fields = [
      'post_tags' => 'post_tag',
      'post_categories' => 'category',
    ];
    $id = $row->getSourceProperty('id');

    foreach ($fields as $source_property => $vocabulary_name) {
      $query = $this->select('term_relationships', 'tr');
      $query->join('term_taxonomy', 'tt', 'tt.term_id = tr.term_taxonomy_id');
      $query->fields('tr', ['term_taxonomy_id'])
        ->condition('tr.object_id', $id)
        ->condition('tt.taxonomy', $vocabulary_name);
      $result = $query->execute();

      if ($result) {
        $terms = $result->fetchCol();
        $row->setSourceProperty($source_property, $terms);
      }
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      'id' => array(
        'type' => 'integer',
        'alias' => 'p',
      ),
    );
  }

}
