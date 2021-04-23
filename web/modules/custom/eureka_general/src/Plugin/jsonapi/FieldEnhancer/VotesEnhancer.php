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

    // Get the node's UUID out of the route.
    $uuid = \Drupal::routeMatch()->getRawParameter('entity');

    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
    $entity_repository = \Drupal::service('entity.repository');
    $entity = $entity_repository->loadEntityByUuid('node', $uuid);

    // Get the votes of the current node.
    $votes = $voting_service->getResults('node', $entity->id());
    $votes = reset($votes);

    // Look for existing votes on the node by the current user.
    $user_votes = \Drupal::entityTypeManager()->getStorage('vote')->loadByProperties([
      'type' => 'updown',
      'entity_id' => $entity->id(),
      'user_id' => \Drupal::currentUser()->id(),
    ]);

    /** @var \Drupal\votingapi\Entity\Vote $votie */
    $user_vote = reset($user_votes);

    // The data we want to return.
    $data = [
      'total' => $votes['vote_count'],
      'average' => $votes['vote_average'],
      'sum' => $votes['vote_sum'],
      'count_up' => $votes['rate_count_up'],
      'user_vote_id' => $user_vote ? $user_vote->id() : '',
      'user_vote_source' => $user_vote ? $user_vote->getSource() : '',
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
