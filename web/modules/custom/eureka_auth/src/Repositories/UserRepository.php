<?php

namespace Drupal\eureka_auth\Repositories;

use Defuse\Crypto\Core;
use Drupal\simple_oauth\Entities\UserEntity;
use Drupal\user\UserAuthInterface;
use GuzzleHttp\Exception\RequestException;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;

class UserRepository implements UserRepositoryInterface {

  /**
   * @var \Drupal\user\UserAuthInterface
   */
  protected $userAuth;

  /**
   * UserRepository constructor.
   *
   * @param \Drupal\user\UserAuthInterface $user_auth
   *   The service to check the user authentication.
   */
  public function __construct(UserAuthInterface $user_auth) {
    $this->userAuth = $user_auth;
  }


  /**
   * Get a user entity.
   *
   * @param $hash
   *   The hashed password.
   * @param $username
   *   The username.
   * @param $timestamp
   *   The timestamp.
   * @param $grantType
   *   The grant type.
   * @param ClientEntityInterface $clientEntity
   *   The client entity.
   *
   * @return UserEntityInterface|null
   */
  public function getUserEntityByHash($hash, $username, $timestamp, $grantType, ClientEntityInterface $clientEntity) {
    $UserEntity = new UserEntity();

    try {
      /** @var \Drupal\user\Entity\User $user */
      $user = user_load_by_name($username);

      if ($user) {
        $is_authenticated = $this->validateHash($user, $timestamp, $hash);

        $user_id  = $user->id();
        $UserEntity->setIdentifier($user_id);
        if ($is_authenticated) {

          if (!$user->isActive() && !$user->getLastAccessedTime()) {
            $user->activate();
          }

          return $UserEntity;
        }

        return NULL;
      }
      else {
        return NULL;
      }
    }
    catch (RequestException $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity) {
    // TODO: Use authenticateWithFloodProtection when #2825084 lands.
    if ($uid = $this->userAuth->authenticate($username, $password)) {
      $user = new UserEntity();
      $user->setIdentifier($uid);

      return $user;
    }
    throw OAuthServerException::invalidCredentials();
  }

  /**
   * Validates a user password by hash.
   *
   * @param $user
   *   A user entity.
   * @param $timestamp
   *   A timestamp.
   * @param $hash
   *   A hashed password.
   *
   * @return bool
   */
  private function validateHash($user, $timestamp, $hash) {
    $timeout = \Drupal::config('user.settings')->get('password_reset_timeout');

    if (time() > ($timestamp + $timeout)) {
      return FALSE;
    }
    if (Core::hashEquals($hash, user_pass_rehash($user, $timestamp))) {
      return TRUE;
    }

    return FALSE;
  }

}
