<?php

namespace Drupal\eureka_general\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\Cache;

/**
 * Class GeneralSettingsCacheableDependency.
 *
 * @package Drupal\eureka_general\Plugin\rest\resource
 */
class GeneralSettingsCacheableDependency implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags[] = 'config:eureka_general.settings';

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

}
