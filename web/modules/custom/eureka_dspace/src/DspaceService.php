<?php

namespace Drupal\eureka_dspace;

use Drupal\Core\State\State;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

Class DspaceService {

  const ENDPOINT = 'http://api.dspace.poc.euraknos.cf/server/api/authn/login';
  const USER = 'admin@euraknos.cf';
  const PASSWORD = 'euraknos4567';

  /**
   * The state.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * The logger interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;


  public function __construct(State $state, LoggerInterface $logger, Client $http_client) {
    $this->state = $state;
    $this->logger = $logger;
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('logger.eureka_dspace'),
      $container->get('http_client')
    );
  }

  /**
   * @return array|false|mixed
   */
  public function authenticate() {
    $token = [];
    try {
      $response = $this->httpClient->request(
        'POST',
        self::ENDPOINT, [
          'form_params' => [
            'user' => self::USER,
            'password' => self::PASSWORD,
          ],
        ]
      );

      $headers = $response->getHeaders();
      if (isset($headers['Authorization'])) {
        $token = reset($headers['Authorization']);
        $this->state->set('dspace.token', $token);
      }
    }
    catch (GuzzleException $e) {
      $this->logger->error($e);
    }

    return $token;
  }
}
