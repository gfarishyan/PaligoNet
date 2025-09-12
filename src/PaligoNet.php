<?php

namespace Gfarishyan\PaligoNet;

use Gfarishyan\PaligoNet\Models\Folder;
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
          'auth' => [$this->configuration->getUsername(), $this->configuration->getApiKey(), 'basic'],
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

      $resources = $this->getResource($endpoint, 'folders', $filter, $paginate);

      if (empty($resources)) {
          return [];
      }

      foreach ($resources as $resource) {
        $folders[] = new Folder($resource);
      }

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
  public function getResource($endpoint, $resource_type, $filter = [], $paginate = true) {
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
        $contents = $response->getBody()->getContents();
        if (!empty($contents)) {
          $response_content = json_decode($contents, true);
        }

        if (!empty($response_content[$resource_type])) {
          $resources = array_merge($resources, $response_content[$resource_type]);
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