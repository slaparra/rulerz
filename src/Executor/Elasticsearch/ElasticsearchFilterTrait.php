<?php

namespace RulerZ\Executor\Elasticsearch;

use RulerZ\Context\ExecutionContext;
use RulerZ\Result\IteratorTools;

trait ElasticsearchFilterTrait
{
    abstract protected function execute($target, array $operators, array $parameters);

    /**
     * {@inheritdoc}
     */
    public function applyFilter($target, array $parameters, array $operators, ExecutionContext $context)
    {
        return $this->execute($target, $operators, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function filter($target, array $parameters, array $operators, ExecutionContext $context)
    {
        /** @var array $searchQuery */
        $searchQuery = $this->execute($target, $operators, $parameters);

        /** @var \Elasticsearch\Client $target */
        $results = $target->search([
            'index' => $context['index'],
            'type' => $context['type'],
            'body' => ['query' => $searchQuery],
        ]);

        if (empty($results['hits'])) {
            return IteratorTools::fromArray([]);
        }

        return IteratorTools::fromArray(array_map(function ($result) {
            return $result['_source'];
        }, $results['hits']['hits']));
    }
}
