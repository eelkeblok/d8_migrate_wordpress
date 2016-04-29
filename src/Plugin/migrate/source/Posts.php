<?php

/**
 * @file
 * Contains \Drupal\migrate_wordpress\Plugin\migrate\source\Posts.
 */

namespace Drupal\migrate_wordpress\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
use Drupal\Core\Database\Query\Condition;

/**
 * Extract posts from Wordpress database.
 *
 * @MigrateSource(
 *   id = "posts"
 * )
 */
class Posts extends DrupalSqlBase {

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
