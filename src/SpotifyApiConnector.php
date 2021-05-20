<?php

namespace Drupal\spotify_integration;

use Drupal\Core\Config\ConfigFactory;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * SpotifyApiConnector service.
 */
class SpotifyApiConnector {

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Constructs a SpotifyApiConnector object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   The configuration manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The Drupal logger service.
   */
  public function __construct(ClientInterface $http_client, ConfigFactory $config, LoggerChannelFactoryInterface $logger) {
    $this->httpClient = $http_client;
    $this->config = $config;
    $this->logger = $logger;
  }

  /**
   * Fetches $number of Artists via Spotify API.
   */
  public function fetchArtists($number) {
    $client = $this->httpClient;

    $token = $this->getAuthBearerToken();

    try {
      $response = $client->request(
            "GET",
            "https://api.spotify.com/v1/browse/new-releases",
            [
              "query" => [
                "limit" => $number,
                "country" => "UK",
              ],
              "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => "Bearer " . $token,
              ],
            ]
        );
    }
    catch (\Exception $e) {
      $this->logger->get("spotify_integration")->error($e->getMessage());
      return [];
    }

    $response_body = json_decode($response->getBody(), TRUE);
    $albums = $response_body["albums"]["items"];

    $artists = array_map(
          function ($album) {
              $artist_arr = [
                'name' => $album["artists"][0]["name"],
                'id' => $album["artists"][0]["id"],
              ];

              return $artist_arr;
          }, $albums
      );

    return $artists;

  }

  /**
   * Fetches Artist info via Spotify API using $artist_id.
   */
  public function fetchArtistInfo($artist_id) {
    $client = $this->httpClient;

    $token = $this->getAuthBearerToken();

    try {
      $response = $client->request(
              "GET",
              "https://api.spotify.com/v1/artists/" . $artist_id,
              [
                "headers" => [
                  "Accept" => "application/json",
                  "Content-Type" => "application/json",
                  "Authorization" => "Bearer " . $token,
                ],
              ]
          );

    }
    catch (\Exception $e) {
      $this->logger->get("spotify_integration")->error($e->getMessage());
      return [];
    }

    $artist = json_decode($response->getBody(), TRUE);

    return $artist;
  }

  /**
   * Get Spotify API Auth Bearer Token.
   */
  private function getAuthBearerToken() {

    $client = $this->httpClient;

    $cid = $this->config->get("spotify_integration.settings")->get("spotify_clientid");
    $cs = $this->config->get("spotify_integration.settings")->get("spotify_clientsecret");

    $auth_string = base64_encode($cid . ":" . $cs);

    try {
      $response = $client->request(
            "POST", "https://accounts.spotify.com/api/token", [
            'form_params' => [
              "grant_type" => "client_credentials",
            ],
            'headers' => [
              'Content-Type' => 'application/x-www-form-urlencoded',
              'Authorization' => 'Basic ' . $auth_string,
            ]
            ]
        );
    }
    catch (\Exception $e) {
      $this->logger->get("spotify_integration")->error($e->getMessage());
      return "";
    }

    $response_body = json_decode($response->getBody(), TRUE);

    return $response_body['access_token'];
  }

}
