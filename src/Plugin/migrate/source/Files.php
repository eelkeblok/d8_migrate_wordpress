<?php

/**
 * @file
 * Contains \Drupal\migrate_wordpress\Plugin\migrate\source\Files.
 */

namespace Drupal\migrate_wordpress\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Extract files from Wordpress database.
 *
 * @MigrateSource(
 *   id = "files"
 * )
 */
class Files extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('posts', 'p')
      ->fields('p', array_keys($this->postFields()))
      ->condition('p.post_type', 'attachment');
    $query->join('postmeta', 'pmfn', "p.ID = pmfn.post_id AND pmfn.meta_key = '_wp_attached_file'");
    $query->leftJoin('postmeta', 'pmmd', "p.ID = pmmd.post_id AND pmmd.meta_key = '_wp_attachment_metadata'");
    $query->addField('pmfn', 'meta_value', 'filename');
    $query->addField('pmmd', 'meta_value', 'filemetadata');

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
      'id' => $this->t('Attachment ID'),
      'post_title' => $this->t('File name'),
      'post_author' => $this->t('Uploaded by (uid)'),
      'post_date' => $this->t('Upload date'),
      'post_mime_type' => $this->t('MIME Type'),
      'guid' => $this->t('File location'),
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
    $source = $row->getSource();
    $filemetadata = unserialize($source['filemetadata']);

    foreach ($filemetadata as $key => $value) {
      $row->setSourceProperty($key, $value);
    }

    // Check if file is present. If not, skip. If so, determine file size.
    // We'll assume the files are located in the public location.
    $file_location = 'public://' . $filemetadata['file'];
    if (file_exists($file_location)) {
      $row->setSourceProperty('filesize', filesize($file_location));
      $row->setSourceProperty('filelocation', $file_location);
    }
    else {
      // Skip.
      return FALSE;
    }

    // Remove the path from the filename field so we have just the name
    // available.
    $parts = explode('/', $source['filename']);
    $row->setSourceProperty('filename', array_pop($parts));

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
