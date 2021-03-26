<?php

namespace Drupal\eureka_general\Plugin\rest\resource;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get all active languages.
 *
 * @RestResource(
 *   id = "eureka_languages",
 *   label = @Translation("Languages (eureka)"),
 *   uri_paths = {
 *     "canonical" = "/api/languages"
 *   }
 * )
 */
class LanguagesResource extends ResourceBase {

  /**
   * An instance of the Logger Interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The langauge manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->logger = $logger;
    $this->languageManager = $language_manager;
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
      $container->get('language_manager')
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

    $data = $this->languageManager->getNativeLanguages();

    // Return the JSON response.
    return new ResourceResponse($data);
  }

}
