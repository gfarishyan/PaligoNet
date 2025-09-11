<?php

namespace Gfarishyan\PaligoNet;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class PaligoNet {
  protected $client;

  protected Configuration $configuration;

  public function __construct(Configuration $configuration)
  {
      $this->configuration = $configuration;
      $this->client = new Client([
          'base_uri' => $this->configuration->getUrl(),
          'auth' => [$this->configuration->getUsername(), $this->configuration->getApiKey(), 'basic']
      ]);
  }

    /**
     * @method listFolders().
     * Return list of folders.
     * @param
     *   int parent
     *    The folder parent id
     *   bool recurse
     *    loop over children
     *
     */
    public function listFolders($filters = [], $page = 1, $per_page = 50, $paginate = true) {
      $endpoint = 'folders';
      $filter = [
        'page' => $page,
        'per_page' => $per_page,
      ];

      if (isset($filters['parent'])) {
        $filter['parent'] = $filters['parent'];
      }

      $folders = $this->getResource($endpoint, $filter, $paginate);
      return $folders;
    }

    /**
     * @method listDocuments.
     * retrieve list of documents based on specified filter.
     */
  public function listDocuments($filter = []) {
    $filter += [
      'per_page' => 50,
      'page' => 1
    ];

  }

    /**
     * Main get request.
     * @param $endpoint
     * @param $filter
     * @param $paginate
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
  public function getResource($endpoint, $filter = [], $paginate = true) {
    $resources = [];
    $delta = 0;
    do {
      $sleep_seconds = 0;
      $response_content = [];
      try {
        $response = $this->client->get($endpoint, ['query' => $filter]);
        if ($response->getHeader('Retry-After')) {
          $sleep_seconds = $response->getHeader('Retry-After');
        }
        $response_content = $response->getBody()->getContents();
        if (!empty($response_content['resources'])) {
          $resources = array_merge($resources, $response_content['resources']);
        }
      } catch (RequestException $e) {
          throw new \Exception($e->getMessage(), $e->getCode());
      }

      if ($sleep_seconds && $paginate) {
        $sleep_seconds += 10; //add at least 10 seconds gap to retry
        sleep($sleep_seconds);
      }

      if (!empty($response_content['next_page'])) {
        $filter['page']++;
      }
    } while($paginate);

    return $resources;
  }
}