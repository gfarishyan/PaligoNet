<?php

namespace Gfarishyan\PaligoNet;

use Gfarishyan\PaligoNet\Models\Document;
use Gfarishyan\PaligoNet\Models\Folder;
use Gfarishyan\PaligoNet\Models\Variable;
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
     * Wrapper for paligo /search endpoint.
     * src https://api.paligo.net/en/index-en.html#UUID-ee4c04d6-2cce-9bb6-ea2d-551e783b949a
     * @param $filters
     *    list of filters described as paligo search
     *    filter structure
     *    the keys of filter are property names while values are array of
     *      operator and value to search for.
     *    e.g.
     *     $filters = ['created_at' => [['>=', 'abc']]
     *
     * @param $page
     * @param $per_page
     * @param $paginate
     * @return void
     */

  public function searchDocuments($filters = [], $page=1, $per_page=50, $paginate=true) {
     if (empty($filters)) {
         throw new \Exception("Filters is required for document search");
     }
     $data = [
         'per_page' => $per_page,
         'page' => $page,
         'resource' => 'documents',
         'where' => [],
     ];

     foreach ($filters as $property => $conditions) {
         foreach ($conditions as $condition) {
             $op = match ($condition[0]) {
                 'equils', 'eq', '=' => 'equals',
                 'has' => 'has',
                 'before', '<=', '<' => 'before',
                 'after', '>=', '>' => 'after',
                 'between' => 'between',
                 default => '',
             };

             if (empty($op)) {
                 throw new \Exception("Filter $property does not have property condition operator");
             }

             if ($op == 'between' && !is_array($condition[1])) {
                 throw new \Exception("$property - Between operator requires array of values.");
             }

             $data['where'][] = [
                 'property' => $property,
                 'operator' => $op,
                 'value' => $condition[1],
             ];
         }
     }

     if (empty($data['where'])) {
         throw new \Exception("Search endpoint requires filters.");
     }
     
     $response = $this->postResource('search', 'items', $data, $paginate);
     $documents = [];
     if ($response) {
         foreach ($response as $document) {
            $documents[] = new Document($document);
         }
         return $documents;
     }
     
     return $response;
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
  public function listDocuments($filter = [], $page = 1, $per_page = 50, $paginate = true) {
    $filter += [
      'per_page' => 50,
      'page' => 1
    ];

    if (!empty($filter['created'])) {
      if (!empty($filter['created']['start'])) {
        $filter['created_start_at'] = $this->getUnixtTimestamp($filter['created']['start']);
      }

      if (!empty($filter['created']['end'])) {
        $filter['created_end_at'] = $this->getUnixtTimestamp($filter['created']['end']);
      }
      unset($filter['created']);
    }

    if (!empty($filter['modified'])) {
      if (!empty($filter['modified']['start'])) {
        $filter['modified_at'] = $this->getUnixtTimestamp($filter['modified']['start']);
      }

      if (!empty($filter['modified']['end'])) {
        $filter['modified_at_end'] = $this->getUnixTimestamp($filter['modified']['end']);
      }
      unset($filter['modified']);
    }

    //to list documents we need to get folder
    $resource = $this->getResource('documents', 'documents', $filter, $paginate);
    if (empty($resource)) {
      return [];
    }

    $documents = [];

    foreach ($resource as $document) {
      $documents[] = new Document($document);
    }
    return $documents;
  }

  public function getDocument($document_id): ?Document {
    $resource = $this->getResource('documents/' . $document_id, null);

    if (empty($resource)) {
        return null;
    }

    return new Document($resource);
  }

  public function getFolder($folderId): ?Folder {
    $endpoint = 'folders/' . $folderId;
    $resource = $this->getResource($endpoint, 'folder');

    if (empty($resource)) {
      return null;
    }

    $children = !empty($resource['children']) ? $resource['children'] : [];
    unset($resource['children']);
    $folder = new Folder($resource);

    if (!empty($children)) {
      $childrens = [];
      foreach ($children as $child) {
         if ($child['type'] == 'folder') {
           $childrens[] = new Folder($child);
         } elseif ($child['type'] == 'document' || $child['type'] == 'publication' || $child['type'] == 'topic') {
           $childrens[] = new Document($child);
         }
      }

      if (!empty($childrens)) {
        $folder->set('children', $childrens);
      }
    }

    return $folder;
  }

  public function listVariableValues($filter = [], $page = 1, $per_page = 50, $paginate = true)
  {
      $filter += [
          'per_page' => 50,
          'page' => 1
      ];
      $resource = $this->getResource('variablevalues', 'variablevalues', $filter, $paginate);
      if (empty($resource)) {
          return [];
      }

      $variables = [];

      foreach ($resource as $variable) {
          if ($variable['type'] == 'image') {
              continue;
          }
          $var = new Variable($variable);
          $variables[] = $var;
      }
      return $variables;
  }

    /**
     * get translation export status
     * @param $id
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
  public function getTranslationExportStatus($id) {
    return $this->getResource('translationexports/' . $id, null, [], false);
  }

  public function downloadTranslationExport($url, $destination) {
    return $this->client->request('GET', $url, [
        'sink' => $destination,
    ]);
  }

    /**
     * Generates translation for document.
     * @param $document_id
     * @return void
     */
  public function createTranslationExport($document_id, $src_language, $target_languages) {
    $export = [
        'document' => $document_id,
        'source' => $src_language,
        'target' => $target_languages,
        'format' => 'xliff',
        'approved' => true,
        'fuzzy' => false,
        'force' => false,
        'preview' => false,
        'varinfo' => true,
    ];
    $response = $this->postResource('translationexports', null, $export);
    return $response;
  }

    /**
     * @param $time_string
     * @return int
     */
  public function getUnixtTimestamp($time_string): ?int {
    if ($time_string instanceof \DateTime) {
      return $time_string->getTimestamp();
    }

    if (is_numeric($time_string)) {
      return (int) $time_string;
    }

    return strtotime($time_string);
  }
    /**
     * Main get request.
     * @param $endpoint
     * @param $filter
     * @param $paginate
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
  public function getResource($endpoint, $resource_type=null, $filter = [], $paginate = true) {
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

        if (!empty($response_content)) {
          $content = $resource_type ? $response_content[$resource_type] : $response_content;
          $resources = array_merge($resources, $content);
        }


        if (!empty($response_content['next_page'])) {
          $filter['page']++;
        } else {
          $paginate = false;
        }
      } catch (RequestException $e) {
          throw new \Exception($e->getMessage(), $e->getCode());
      }

      if ($sleep_seconds && $paginate) {
        $sleep_seconds += 10; //add at least 10 seconds gap to retry
        sleep($sleep_seconds);
      }
    } while($paginate);

    return $resources;
  }

    /**
     *
     * @param $endpoint
     * @param $resource_type
     * @param $paginate
     * @return void
     */
  public function postResource($endpoint, $resource_type = null, array $body = [], $paginate = false) {
      $resources = [];
      $delta = 0;
      do {
          $json = json_encode($body);
          $sleep_seconds = 0;
          $response_content = [];
          try {
              $response = $this->client->post($endpoint, [
                  'body' => $json
              ]);

              if ($response->getHeader('Retry-After')) {
                  $sleep_seconds = $response->getHeader('Retry-After');
              }
              $contents = $response->getBody()->getContents();
              if (!empty($contents)) {
                  $response_content = json_decode($contents, true);
              }

              if (!empty($response_content)) {
                  $content = $resource_type ? $response_content[$resource_type] : $response_content;
                  $resources = array_merge($resources, $content);
              }

              if (!empty($response_content['total_pages'])) {
                  $body['page']++;
                  if ($body['page'] > $response_content['total_pages']) {
                      $paginate = false;
                  }
              } else {
                  $paginate = false;
              }
          } catch (RequestException $e) {
              throw new \Exception($e->getMessage(), $e->getCode());
          }

          if ($sleep_seconds && $paginate) {
              $sleep_seconds += 10; //add at least 10 seconds gap to retry
              sleep($sleep_seconds);
          }
      } while($paginate);

      return $resources;
  }

}