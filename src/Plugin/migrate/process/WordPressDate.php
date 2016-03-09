<?php

/**
 * @file
 * Contains \Drupal\migrate_wordpress\Plugin\migrate\process\WordPressDate.
 */

namespace Drupal\migrate_wordpress\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin turns a WordPress date (datetime database column) into a UNIX
 * timestamp. Provide a source value to act as a default using the default
 * property.
 *
 * @MigrateProcessPlugin(
 *   id = "wordpress_date"
 * )
 */

class WordPressDate extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($value) || $value == '0000-00-00 00:00:00') {
      $source = $row->getSource();
      if (!empty($this->configuration['fallback']) && !empty($source[$this->configuration['fallback']])) {
        $value = $source[$this->configuration['fallback']];
      }
      else {
        $value = '1970-01-01 12:00:00';
      }
    }

    return strtotime($value);
  }
}
