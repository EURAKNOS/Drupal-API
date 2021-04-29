<?php

namespace Drupal\eureka_auth\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\UserInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Represents the user reset flow as a resource.
 *
 * @RestResource(
 *   id = "user_resetpassword",
 *   label = @Translation("User reset password"),
 *   uri_paths = {
 *     "canonical" = "/api/user/{entity_id}/resetpass",
 *   },
 * )
 */
class ResetPasswordResource extends ResourceBase {

  /**
   * Responds to user reset password GET request.
   *
   * @param $entity_id
   *   The entity uuid..
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  public function get($entity_id) {
    /** @var \Drupal\user\Entity\User $account */
    $account = \Drupal::service('entity.repository')->loadEntityByUuid('user', $entity_id);

    if ($account) {

      $this->sendEmailNotifications($account);

      return new ResourceResponse($account, 200);
    }

    throw new BadRequestHttpException('The user does not exist.');
  }

  /**
   * Sends confirmation email for user that was registered.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account.
   */
  protected function sendEmailNotifications(UserInterface $account) {
    _user_mail_notify('password_reset', $account);
  }

}
