<?php

/**
 * @file
 * Contains \Drupal\f1_screenshot_service\Controller\F1ScreenshotService.
 */

namespace Drupal\f1_screenshot_service\Controller;

use Drupal\Core\Controller\ControllerBase;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class F1ScreenshotService extends ControllerBase {
  /**
   * The URLBox API Key.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * The URLBox API secret.
   *
   * @var string
   */
  protected $apiSecret;

  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Indexed array of host we may take screenshots of.
   *
   * @var array
   */
  protected $allowedHosts;

  /**
   * Indexed array of allowed options.
   *
   * @var array
   */
  protected $allowedOptions;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(Client $http_client, RequestStack $request_stack, $api_key, $api_secret, $allowed_hosts, $allowed_options) {
    $this->httpClient = $http_client;
    $this->request = $request_stack->getCurrentRequest();
    $this->apiKey = $api_key;
    $this->apiSecret = $api_secret;
    $this->allowedHosts = array_map('trim', explode(',', $allowed_hosts)) ?? [];
    $this->allowedOptions = array_map('trim', explode(',', $allowed_options)) ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('request_stack'),
      getenv('URLBOX_API_KEY'),
      getenv('URLBOX_SECRET'),
      getenv('URLBOX_ALLOWED_HOSTS'),
      getenv('URLBOX_ALLOWED_OPTIONS'),
    );
  }

  /**
   * Builds a query string to be passed to URLBox service.
   *
   * @param array $params
   *   An array of options keyed by option name.
   * @throws Exception
   *   An exception is thrown if an invalid set of options is passed.
   * @return void
   */
  protected function buildOptionsString($params) {
    $keys = array_keys($params);
    $not_allowed = array_diff($keys, $this->allowedOptions);

    if (is_null($params['url'])) {
      throw new Exception('Option "url" is required!');
    }

    $parsed_url = parse_url($params['url']);
    if (!in_array($parsed_url['host'], $this->allowedHosts)) {
      throw new Exception('Requesting a resource on host "'  . $parsed_url['host'] . '" is not permitted.');
    }

    if (count($not_allowed)) {
      throw new Exception('The following options are not permitted: ' . implode(', ', $not_allowed) . '.');
    }

    return http_build_query($params);
  }

  /**
   * Validates the request and routes it to to the URLBox service.
   *
   * @param string $type
   *   One of [ 'pdf', 'png' ].
   * @return Response
   *   Either a binary response (success) or a text response describing an error.
   */
  protected function render($type) {
    parse_str($this->request->getQueryString(), $params);

    try {
      $params = $this->buildOptionsString($params);
    } catch (Exception $e) {
      return new Response($e->getMessage(), 400);
    }

    $request_url = "https://api.urlbox.io/v1/$this->apiKey/$type?$params";

    try {
      return $this->httpClient->request('GET', $request_url);
    } catch (Exception $e) {
      return new Response('Error taking screenshot.', 400);
    }
  }

  /**
   * Exposed via Symfony in the routing YAML file.
   *
   * @return Response
   *   Either a binary response (success) or a text response describing an error.
   */
  public function renderPDF() {
    return $this->render("pdf");
  }

  /**
   * Exposed via Symfony in the routing YAML file.
   *
   * @return Response
   *   Either a binary response (success) or a text response describing an error.
   */
  public function renderPNG() {
    return $this->render("png");
  }
}
