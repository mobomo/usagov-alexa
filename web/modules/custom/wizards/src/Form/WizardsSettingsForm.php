<?php

namespace Drupal\wizards\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure settings for the Wizards module.
 */
class WizardsSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   * 
   * @var string
   */
  const SETTINGS = 'wizards.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormid() {
    return 'wizards_admin_settings';
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

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $wizardService = \Drupal::service('wizards.wizard');
    $config = $this->config(static::SETTINGS);

    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm( array &$form, FormStateInterface $form_state ) {
    // $form_state->setErrorByName('wizards_field', $this->t('My Error Message'));
  }

}