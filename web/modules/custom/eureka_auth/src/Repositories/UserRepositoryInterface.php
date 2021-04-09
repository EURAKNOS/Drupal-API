<?php

namespace Drupal\eureka_auth\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;

interface UserRepositoryInterface {

  /**
   * Get a user entity.
   *
   * @param string $hash
   * @param string $username
   * @param string $timestamp
   * @param string $grantType
   * @param ClientEntityInterface $clientEntity
   *
   * @return UserEntityInterface|null
   */
  public function getUserEntityByHash($hash, $username, $timestamp, $grantType, ClientEntityInterface $clientEntity);

  /**
   * Get a user entity.
   *
   * @param string $username
   * @param string $password
   * @param string $grantType
   * @param ClientEntityInterface $clientEntity
   *
   * @return UserEntityInterface|null
   */
  public function getUserEntityByUserCredentials(
    $username,
    $password,
    $grantType,
    ClientEntityInterface $clientEntity
  );
}
