<?php

namespace FOS\ElasticaBundle\Tests\ObjectPersister;

use FOS\ElasticaBundle\Persister\ObjectPersister;

class InvalidObjectPersister extends ObjectPersister
{
    public function transformToElasticaDocument($object)
    {
        throw new \BadMethodCallException('Invalid transformation');
    }
}
