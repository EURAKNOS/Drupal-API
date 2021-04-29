<?php

namespace Drupal\eureka_general\Plugin\rest\resource;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get an entities translations.
 *
 * @RestResource(
 *   id = "eureka_entity_translations",
 *   label = @Translation("Entity translations (eureka)"),
 *   uri_paths = {
 *     "canonical" = "/api/{entity_type}/{entity_id}/translations"
 *   }
 * )
 */
class EntityTranslationResource extends ResourceBase {

  /**
   * An instance of the Logger Interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, LanguageManagerInterface $language_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->logger = $logger;
    $this->languageManager = $language_manager;
    $this->entityRepository = $entity_repository;
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
      $container->get('language_manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * Responds to GET request.
   *
   * @param $entity_type
   *   The entity type.
   * @param $entity_id
   *   The entity uuid.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Return REST response of social media channels.
   */
  public function get($entity_type, $entity_id) {
    $data = [];

    $entity = $this->entityRepository->loadEntityByUuid($entity_type, $entity_id);

    if ($entity) {
      $languages = $this->languageManager->getLanguages();

      // Loop each language and check for available translations.
      foreach ($languages as $language) {
        $langcode = $language->getId();

        if ($entity->hasTranslation($langcode)) {
          $entity = $entity->getTranslation($langcode);

          $path = $entity->get('path');

          // Add the translation path to the response.
          $data[$langcode] = $path ? $path->alias : '/';
        }
      }
    }

    // Return the JSON response.
    return new ResourceResponse($data);
  }

}
