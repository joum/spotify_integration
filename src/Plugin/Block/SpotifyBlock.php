<?php

namespace Drupal\spotify_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\spotify_integration\SpotifyApiConnector;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "spotify_block",
 *   admin_label = @Translation("Spotify Artists Block"),
 *   category = @Translation("Spotify Integration")
 * )
 */
class SpotifyBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Spotify API Connector service.
   *
   * @var \Drupal\spotify_integration\SpotifyApiConnector
   */
  protected $apiConnector;

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SpotifyApiConnector $api_connector) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->apiConnector = $api_connector;
  }

  /**
   * Creation and dep. injection.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('spotify_integration.api_connector')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $number_of_artists = $config['spotify_artists_count'] ?? 5;

    $artists = $this->apiConnector->fetchArtists($number_of_artists);

    $renderable = [
      '#theme' => 'spotify_block',
      '#artists' => $artists,
      '#attached' => [
        "library" => ["spotify_integration/spotify_integration_block"],
      ],
    ];

    return $renderable;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['spotify_artists_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of artists'),
      '#description' => $this->t('How many artists should be fetched from the Spotify API'),
      '#default_value' => $config['spotify_artists_block'] ?? 5,
      '#min' => 1,
      '#max' => 20,
      '#step' => 1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if ($form_state->getValue('spotify_artists_count') < 1 || $form_state->getValue('spotify_artists_count') > 20) {
      $form_state->setErrorByName('spotify_artists_count', $this->t('Only 1 to 20 artists allowed.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['spotify_artists_count'] = $values['spotify_artists_count'];
  }

}
