<?php

namespace Drupal\migrate_wordpress\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Component\Utility\Unicode;

/**
 * This plugin translates WordPress style read more comments (used to define
 * where the front page summary ends and a "Read more" link is displayed) into
 * Drupal style (WP uses <!--more-->, Drupal uses <!--break-->).
 *
 * @MigrateProcessPlugin(
 *   id = "wordpress_readmore"
 * )
 */

class WordPressReadMore extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (Unicode::strpos($value, '<!--more-->') !== FALSE) {
      $value = str_replace('<!--more-->', '<!--break-->', $value);
    }

    return $value;
  }
}
