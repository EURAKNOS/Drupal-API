<?php

namespace Drupal\eureka_general\Plugin\rest\resource;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get Eureka general settings.
 *
 * @RestResource(
 *   id = "eureka_general_vote_results",
 *   label = @Translation("Vote results (eureka)"),
 *   uri_paths = {
 *     "canonical" = "/api/vote_result/{entity_id}"
 *   }
 * )
 */
class VoteResults extends ResourceBase {

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
   * @param $entity_id
   *   The entity uuid.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Return REST response of social media channels.
   */
  public function get($entity_id) {
    $data = [];
    $user_vote = NULL;

    /** @var \Drupal\votingapi\VoteResultFunctionManager $voting_service */
    $voting_service = \Drupal::service('plugin.manager.votingapi.resultfunction');
    // Get the node's UUID out of the route.
    $uuid = $entity_id;

    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
    $entity_repository = \Drupal::service('entity.repository');

    if ($uuid) {
      $entity_type = 'node';
      $vote_type =  'updown';
      $entity = $entity_repository->loadEntityByUuid($entity_type, $uuid);


      if (!$entity) {
        $entity_type = 'comment';
        $vote_type = 'updown_comments';

        $entity = $entity_repository->loadEntityByUuid('comment', $uuid);
      }

      if ($entity) {
        $votes = 0;
        $id = (int)$entity->id();

        $results = \Drupal::database()->select('votingapi_vote', 'v')
          ->fields('v', ['value'])
          ->condition('entity_type', $entity_type)
          ->condition('entity_id', $id)
          ->condition('type', $vote_type)
          ->execute();

        foreach ($results->fetchAll() as $result) {
          $votes += (int)$result->value;
        }

          // Look for existing votes on the node by the current user.
          $user_votes = \Drupal::entityTypeManager()->getStorage('vote')->loadByProperties([
            'type' => $vote_type,
            'entity_id' => $entity->id(),
            'user_id' => \Drupal::currentUser()->id(),
          ]);


          /** @var \Drupal\votingapi\Entity\Vote $votie */
          $user_vote = reset($user_votes);

          $data = [
            'type' => $vote_type,
            'entity_id' => $id,
            'entity_type' => $entity_type,
            'sum' => $votes,
            'user_vote_id' => $user_vote ? $user_vote->id() : '',
            'user_vote_source' => $user_vote ? $user_vote->getSource() : '',
          ];
      }
    }

    $build = array(
      '#cache' => array(
        'max-age' => 0,
      ),
    );

    return (new ResourceResponse($data))->addCacheableDependency($build);
  }

}
