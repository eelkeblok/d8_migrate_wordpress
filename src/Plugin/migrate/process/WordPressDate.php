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
 * This plugin turns a WordPress data (datetime database column) into a UNIX
 * timestamp. Provide a source value to act as a default using the default
 * property.
 *
 * @MigrateProcessPlugin(
 *   id = "wordpress_data"
 * )
 */

class WordPressDate extends ProcessPluginBase { {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $return = strtotime($value);

    return $return;
  }
}
