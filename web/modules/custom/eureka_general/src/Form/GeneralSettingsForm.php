<?php

namespace Drupal\eureka_general\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GeneralSettingsForm.
 *
 * @package Drupal\eureka_general\Form
 */
class GeneralSettingsForm extends ConfigFormBase {

  const SETTINGS = 'eureka_general.settings';
  const FIELDS = [
    'footer_description',
    'address',
    'copyright'
  ];

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
    return 'eureka_general.settings';
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

    $form['footer_description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Footer description'),
      '#description' => $this->t('A small description of the project. (Used in: Footer)'),
      '#default_value' => $config->get('footer_description') ? $config->get('footer_description')['value'] : NULL,
      '#format' => 'basic_html',
    ];

    $form['address'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Address'),
      '#description' => $this->t('(Used in: Footer)'),
      '#default_value' => $config->get('address') ? $config->get('address')['value'] : NULL,
      '#format' => 'basic_html',
    ];

    $form['copyright'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Copyright text'),
      '#description' => $this->t('(Used in: Footer)'),
      '#default_value' => $config->get('copyright') ? $config->get('copyright') : NULL,
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
