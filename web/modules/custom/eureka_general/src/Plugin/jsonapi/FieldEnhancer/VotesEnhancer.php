<?php

namespace Drupal\eureka_general\Plugin\jsonapi\FieldEnhancer;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\jsonapi_extras\Plugin\ResourceFieldEnhancerBase;
use Drupal\node\Entity\Node;
use Drupal\votingapi\Entity\Vote;
use Drupal\votingapi\Entity\VoteResult;
use Shaper\Util\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds votes specific data to entities.
 *
 * @ResourceFieldEnhancer(
 *   id = "votes_enhancer",
 *   label = @Translation("Votes Enhancer (Eureka)"),
 *   description = @Translation("Adds vote specific data to an entities.")
 * )
 */
class VotesEnhancer extends ResourceFieldEnhancerBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function doUndoTransform($data, Context $context) {
    /** @var \Drupal\votingapi\VoteResultFunctionManager $voting_service */
    $voting_service = \Drupal::service('plugin.manager.votingapi.resultfunction');
    $uuid = \Drupal::routeMatch()->getRawParameter('entity');
    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
    $entity_repository = \Drupal::service('entity.repository');
    $entity = $entity_repository->loadEntityByUuid('node', $uuid);
    $votes = $voting_service->getResults('node', $entity->id());
    $votes = reset($votes);
    $voties = \Drupal::entityTypeManager()->getStorage('vote')->loadByProperties([
      'type' => 'updown',
      'entity_id' => $entity->id(),
      'user_id' => \Drupal::currentUser()->id(),
    ]);

    /** @var \Drupal\votingapi\Entity\Vote $votie */
    $votie = reset($voties);

    $data = [
      'total' => $votes['vote_count'],
      'average' => $votes['vote_average'],
      'sum' => $votes['vote_sum'],
      'count_up' => $votes['rate_count_up'],
      'user_vote_id' => $votie ? $votie->id() : '',
      'user_vote_source' => $votie ? $votie->getSource() : '',
    ];

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  protected function doTransform($data, Context $context) {
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getOutputJsonSchema() {
    return [
      'type' => 'object',
    ];
  }

}
