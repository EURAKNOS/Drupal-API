<?php

namespace Drupal\eureka_general\Plugin\rest\resource;

use Drupal\eureka_general\Form\GeneralSettingsForm;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\translated_config\TranslatedConfigHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get Eureka general settings.
 *
 * @RestResource(
 *   id = "eureka_general_settings",
 *   label = @Translation("General settings (eureka)"),
 *   uri_paths = {
 *     "canonical" = "/api/general_settings"
 *   }
 * )
 */
class GeneralSettingsResource extends ResourceBase {

  /**
   * An instance of the Logger Interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Translated config helper.
   *
   * @var \Drupal\translated_config\TranslatedConfigHelper
   */
  protected $translatedConfigHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, TranslatedConfigHelper $translated_config_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->logger = $logger;
    $this->translatedConfigHelper = $translated_config_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('translated_config.helper')
    );
  }

  /**
   * Responds to GET request.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Return REST response of social media channels.
   */
  public function get() {
    $data = [];

    // TODO once this issue is resolved: https://www.drupal.org/project/drupal/issues/2993984
    //   1) Replace translated_config.helper with config.factory & make sure translations are working properly.
    //   2) Remove the translated_config module.
    $general_settings = $this->translatedConfigHelper->getTranslatedConfig(GeneralSettingsForm::SETTINGS);

    foreach (GeneralSettingsForm::FIELDS as $field) {
      $value = $general_settings->get($field);

      // Some form elements are returned as arrays because they have extra configuration.
      // E.g. text_format holds 2 keys: value & format.
      // In this case we only want to map values to the response.
      $value = is_array($value) ? $value['value'] : $value;

      $data[$field] = $value;
    }

    $response = new ResourceResponse($data);

    $response->addCacheableDependency(new GeneralSettingsCacheableDependency());

    // Return the JSON response.
    return $response;
  }

}
