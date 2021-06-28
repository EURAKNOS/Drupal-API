<?php

namespace Drupal\eureka_dspace\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;

/**
 * Empty items in Dspace use "N.A." instead of being NULL/Empty.
 * This conflicts with some drupal fields (e.g. date fields).
 *
 * @MigrateProcessPlugin(
 *   id = "ignore"
 * )
 */
class Ignore extends ProcessPluginBase {

  const TO_SKIP = [
    'N.A.'
  ];

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if (in_array($value, self::TO_SKIP)) {
      $default_value = $this->configuration['default_value'];

      if ($default_value) {
        return $default_value;
      }

      return NULL;
    }

    return $value;
  }

}
