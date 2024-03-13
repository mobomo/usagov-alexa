<?php

namespace Drupal\alexa_wizard\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure settings for the Alexa Wizard module.
 */
class AlexaWizardSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   * 
   * @var string
   */
  const SETTINGS = 'alexa_wizard.settings';

  /**
   * Default launch message.
   * 
   * @var string
   */
  const DEFAULT_LAUNCH_MESSAGE = 'Welcome to the Alexa Wizard Skill! Which wizard would you like to launch?';

  /**
   * {@inheritdoc}
   */
  public function getFormid() {
    return 'alexa_wizard_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      static::SETTINGS
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildform(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    // TODO add Spanish support
    $form['alexa_wizard_launch_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Launch Message'),
      '#description' => $this->t('What is said when the Alexa Skill launches. Supports SSML.'),
      '#default_value' => $config->get('alexa_wizard_launch_message') ?? static::DEFAULT_LAUNCH_MESSAGE
    ];

    // TODO other variables. Default help message? Checkbox for appending suggestions automatically? Etc.

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $wizardService = \Drupal::service('alexa_wizard.wizard');
    $config = $this->config(static::SETTINGS);

    $config->set('alexa_wizard_launch_message', $wizardService->sanitizeSSMLText($form_state->getValue('alexa_wizard_launch_message')));

    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm( array &$form, FormStateInterface $form_state ) {
    // $form_state->setErrorByName('alexa_wizard_launch_message', $this->t('My Error Message'));
  }

}