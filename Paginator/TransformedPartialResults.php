<?php

namespace FOS\ElasticaBundle\Paginator;

use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Elastica\SearchResultSet;

/**
 * Partial transformed result set.
 */
class TransformedPartialResults extends RawPartialResults
{
    protected $transformer;

    /**
     * @param SearchResultSet                                                           $resultSet
     * @param \FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface $transformer
     */
    public function __construct(SearchResultSet $resultSet, ElasticaToModelTransformerInterface $transformer)
    {
        parent::__construct($resultSet);

        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->transformer->transform($this->resultSet->getResults());
    }
}
