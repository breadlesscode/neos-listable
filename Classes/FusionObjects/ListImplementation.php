<?php
namespace Breadlesscode\Listable\FusionObjects;

use Flowpack\ElasticSearch\ContentRepositoryAdaptor\Eel\ElasticSearchQueryBuilder;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class ListImplementation extends AbstractFusionObject
{
    /**
     * @return int
     */
    protected function getCurrentPage(): int
    {
        return (int) $this->fusionValue('currentPage');
    }

    /**
     * @return int
     */
    protected function getItemsPerPage(): int
    {
        return (int) $this->fusionValue('itemsPerPage');
    }

    /**
     * @return int
     */
    protected function getItemOffset() : int
    {
        return ($this->getCurrentPage() - 1) * $this->getItemsPerPage();
    }

    /**
     * @return bool
     */
    protected function isPaginated() : bool
    {
        return $this->fusionValue('paginated');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function evaluate()
    {
        $query = $this->fusionValue('query');
        $this->runtime->pushContext('query', $query);
        $filters = $this->fusionValue('filters');
        $result = [];

        $this->runtime->pushContext('currentPage', $this->getCurrentPage());
        $this->runtime->pushContext('itemsPerPage', $this->getItemsPerPage());
        $this->runtime->pushContext('itemCount', $query->count());

        if ($this->isPaginated()) {
            $result = $this->getPaginatedResults($query);
        } else {
            $result = $this->getResults($query);
        }

        $this->runtime->pushContext('items', $result);
        $this->runtime->pushContext('pagination', $this->fusionValue('paginationRenderer'));

        return $this->fusionValue('renderer');
    }

    /**
     * @param FlowQuery|ElasticSearchQueryBuilder $query
     * @return \Flowpack\ElasticSearch\ContentRepositoryAdaptor\Eel\ElasticSearchQueryResult|\Traversable
     * @throws \JsonException
     */
    protected function getPaginatedResults($query)
    {
        if ($query instanceof ElasticSearchQueryBuilder) {
            return $this->paginateElasticSearchQuery($query)->execute()->toArray();
        } else if($query instanceof FlowQuery) {
            return $this->paginateFlowQuery($query)->get();
        }

        throw new \Exception('The query type ' . get_class($query) . ' is not supported yet');
    }

    /**
     * @param FlowQuery|ElasticSearchQueryBuilder $query
     * @return \Flowpack\ElasticSearch\ContentRepositoryAdaptor\Eel\ElasticSearchQueryResult|\Traversable
     * @throws \JsonException
     */
    protected function getResults($query)
    {
        if ($query instanceof ElasticSearchQueryBuilder) {
            return $query->execute();
        } else if($query instanceof FlowQuery) {
            return $query->get();
        }

        throw new \Exception('The query type ' . get_class($query) . ' is not supported yet');
    }

    /**
     * @param FlowQuery $query
     * @return FlowQuery
     */
    protected function paginateFlowQuery(FlowQuery $query) : FlowQuery
    {
        return $query->slice(
            $this->getItemOffset(),
            $this->getItemOffset() + $this->getItemsPerPage()
        );
    }

    /**
     * @param ElasticSearchQueryBuilder $query
     * @return ElasticSearchQueryBuilder
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    protected function paginateElasticSearchQuery(ElasticSearchQueryBuilder $query) : ElasticSearchQueryBuilder
    {
        return $query
            ->limit($this->getItemsPerPage())
            ->from($this->getItemOffset());
    }
}
