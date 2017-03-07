<?php

namespace Drupal\migrate_wordpress;

/**
 * @file
 * WpOptionTrait.
 */

/**
 * Retrieve options from the WP database.
 */
trait WpOptionTrait {

  /**
   * Retrieve an option.
   *
   * @param string $option
   *   The name of the option.
   *
   * @return mixed
   *   The value of the option or FALSE if it does not exist..
   */
  protected function getOption($option) {
    $options = &drupal_static(__METHOD__, NULL);

    if (is_null($options)) {
      $query = $this->select('options', 'o')
        ->fields('o', ['option_name', 'option_value']);
      $result = $query->execute();
      $options = $result->fetchAllKeyed();
    }

    if (isset($options[$option])) {
      return $options[$option];
    }

    return FALSE;
  }

}
