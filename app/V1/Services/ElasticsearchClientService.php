<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\ElasticsearchClientService as ClientServiceInterface;
use Elasticsearch\ClientBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ElasticsearchClientService implements ClientServiceInterface
{
    /**
     * @var
     */
    protected $client;

    /**
     * @var
     */
    protected $logger;

    /**
     * Service constructor
     */
    public function __construct()
    {
        $this->makeLogger();
        $this->build();
    }

    /**
     * Build Elasticsearch\Client
     */
    private function build()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([
                config('services.elasticsearch.hosts.default')
            ])
            ->setLogger($this->logger)
            ->build();
    }


    /**
     * Make client logger
     */
    private function makeLogger()
    {
        $this->logger = new Logger('elasticsearch-logger');
        $this->logger->pushHandler(new StreamHandler(storage_path('logs/elastic.log'), Logger::ERROR));
    }

    /**
     * Add document to index
     *
     * @param Array $document
     */
    public function indexDocument($document)
    {
        try {
            $this->updateIndex($document);
        } catch (\Exception $e) {
            $this->logger->addError('Operation index elasticsearch failed' . $e->getMessage());
        }
    }

    /**
     * Verify index exists
     *
     * @param string $indexName
     *
     * @return bool
     */
    public function indexExists($indexName)
    {
        return $this->client->indices()->exists([
            'index' => $indexName
        ]);
    }

    /**
     * Create index
     *
     * @param string $indexName
     */
    public function createIndex($indexName, $mapping = false)
    {
        $params = [
            'index' => $indexName
        ];

        if ($mapping) {
            $params['body'] = [
                'mappings' => $mapping,
            ];
        }

        $this->client->indices()->create($params);
    }

    /**
     * Create index and document in index
     *
     * @param array $document
     *
     * @return array
     */
    public function addToIndex($document)
    {
        return $this->client->index($document);
    }


    /**
     * Create or update document in index
     *
     * @param array $document
     *
     * @return array
     */
    public function updateIndex($document)
    {
        return $this->client->update($document);
    }

    /**
     * Create or update a batch of documents in index
     *
     * @param array $documents
     */
    public function indexBatch($documents)
    {
        return $this->client->bulk($documents);
    }

    /**
     * Delete document from index
     *
     * @param array $params
     *
     * @return mixed
     */
    public function delete($params)
    {
        $response = null;

        try {
            $response = $this->client->delete($params);
        } catch (\Exception $e) {
            //Throws exception when document doesn't exist
        }
        return $response;
    }

    /**
     * Get document from index
     *
     * @param array $params
     *
     * @return mixed
     */
    public function get($params)
    {
        $response = null;

        try {
            $response = $this->client->get($params);
        } catch (\Exception $e) {
            //Throws exception when document doesn't exist
        }
        return $response;
    }

    /**
     * Request aggregations
     *
     * @param $indexName
     * @param $query
     * @param string[] $aggrGroups
     *
     * @return mixed
     */
    public function getAggregations($indexName, $query, $aggrGroups = ['aggr_group'])
    {
        $response = null;

        try {
            $response = $this->client->search([
                'index' => $indexName,
                'body' => $query,
            ]);

            $response = $this->performAggrResponse($response, $aggrGroups);
        } catch (\Exception $e) {
            \Log::error($e);
            $response = null;
        }
        return $response;
    }

    /**
     * Get aggregation response
     *
     * @param $data
     * @param string[] $aggrGroups
     *
     * @return mixed
     */
    public function performAggrResponse($data, $aggrGroups = ['aggr_group'])
    {
        $groups = [];
        if ($data && $data['aggregations']) {
            foreach ($aggrGroups as $groupName) {
                $groupData = $data['aggregations'][$groupName];
                $buckets = $groupData['buckets'];
                $groups[$groupName] = $buckets;
            }
        }

        return $groups;
    }

    /**
     * Delete documents by query
     *
     * @param array $params
     *
     * @return mixed
     */
    public function deleteByQuery($params)
    {
        $response = null;
        try {
            $response = $this->client->deleteByQuery($params);
        } catch (\Exception $e) {
            $this->logger->addError('Operation DELETE BY QUERY failed' . $e->getMessage());
        }
        return $response;
    }
}
