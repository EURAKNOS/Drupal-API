<?php

namespace Drupal\eureka_general\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FrontEndRoutesForm.
 *
 * @package Drupal\eureka_general\Form
 */
class FrontEndRoutesForm extends ConfigFormBase {

  const SETTINGS = 'eureka_general.front_end_routes';

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Form constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configFactory);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eureka_general.front_end_routes';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;

    $config = $this->config(self::SETTINGS);

    $form['base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base url'),
      '#description' => $this->t('Base URL for Eureka Front-end'),
      '#default_value' => $config->get('base_url') ? $config->get('base_url') : NULL,
    ];

    $form['user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User route'),
      '#description' => $this->t('e.g. /user'),
      '#default_value' => $config->get('user') ? $config->get('user') : '/user',
    ];

    $form['password_reset'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password Reset'),
      '#description' => $this->t('e.g. /user/password'),
      '#default_value' => $config->get('password_reset') ? $config->get('password_reset') : '/user/reset',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config(self::SETTINGS);

    foreach ($form_state->cleanValues()->getValues() as $key => $formValue) {
      $config->set($key, $formValue)->save();
    }
  }

}
