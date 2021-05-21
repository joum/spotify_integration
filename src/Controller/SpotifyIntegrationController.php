<?php

namespace Drupal\spotify_integration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\spotify_integration\SpotifyApiConnector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Spotify Integration routes.
 */
class SpotifyIntegrationController extends ControllerBase {

  /**
   * Spotify Api Connector Service.
   *
   * @var \Drupal\spotify_integration\SpotifyApiConnector
   */
  private $apiConnector;

  /**
   * Artist data assoc. array.
   *
   * @var array
   */
  private $artistDataArray;

  /**
   * Constructor.
   */
  public function __construct(SpotifyApiConnector $api_connector) {
    $this->apiConnector = $api_connector;
  }

  /**
   * Create and dep. injection.
   */
  public static function create(ContainerInterface $container) {
    $api_connector = $container->get("spotify_integration.api_connector");
    return new static($api_connector);
  }

  /**
   * Builds the response.
   */
  public function build(string $artist_id) {

    $artist = is_null($this->artistDataArray) ? $this->apiConnector->fetchArtistInfo($artist_id) : $this->artistDataArray;

    $build['content'] = [
      '#theme' => 'spotify_profile',
      '#artist' => $artist,
      '#attached' => [
        "library" => ["spotify_integration/spotify_integration_profile"],
      ],
    ];

    return $build;
  }

  /**
   * Fetches the route's title.
   */
  public function getTitle(string $artist_id) {
    $artist = is_null($this->artistDataArray) ? $this->apiConnector->fetchArtistInfo($artist_id) : $this->artistDataArray;

    return empty($artist) ? $this->t("Not Found") : $artist['name'];
  }

}
