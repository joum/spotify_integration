<?php

namespace Drupal\spotify_integration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Spotify Integration settings for this site.
 */
class SpotifySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spotify_integration_spotify_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['spotify_integration.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['spotify_clientid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify Client ID'),
      '#default_value' => $this->config('spotify_integration.settings')->get('spotify_clientid'),
    ];

    $form['spotify_clientsecret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify clientsecret'),
      '#default_value' => $this->config('spotify_integration.settings')->get('spotify_clientsecret'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('spotify_integration.settings')
      ->set('spotify_clientid', $form_state->getValue('spotify_clientid'))
      ->set('spotify_clientsecret', $form_state->getValue('spotify_clientsecret'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
