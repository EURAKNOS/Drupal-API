<?php

namespace Drupal\eureka_auth\Plugin\Oauth2Grant;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\eureka_auth\Grant\ResetPasswordGrant;
use Drupal\eureka_auth\Repositories\UserRepositoryInterface;
use Drupal\simple_oauth\Plugin\Oauth2GrantBase;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Reset password grant plugin.
 *
 * @Oauth2Grant(
 *   id = "reset_password",
 *   label = @Translation("Reset Password Grant")
 * )
 */
class ResetPassword extends Oauth2GrantBase {

  /**
   * @var \Drupal\eureka_auth\Repositories\UserRepositoryInterface
   */
  protected $userRepositoryInterface;

  /**
   * @var \League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface
   */
  protected $refreshTokenRepository;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ResetPassword constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UserRepositoryInterface $user_repository, RefreshTokenRepositoryInterface $refresh_token_repository, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userRepositoryInterface = $user_repository;
    $this->refreshTokenRepository = $refresh_token_repository;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('eureka_auth.repositories.user'),
      $container->get('simple_oauth.repositories.refresh_token'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getGrantType() {
    $grant = new ResetPasswordGrant($this->userRepositoryInterface, $this->refreshTokenRepository);
    $settings = $this->configFactory->get('simple_oauth.settings');
    $grant->setRefreshTokenTTL(new \DateInterval(sprintf('PT%dS', $settings->get('refresh_token_expiration'))));
    return $grant;
  }

}
