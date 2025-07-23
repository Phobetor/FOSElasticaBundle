<?php

namespace FOS\ElasticaBundle\Tests\Transformer\ModelToElasticaAutoTransformer;

class CastableObject
{
    public $foo;

    public function __toString()
    {
        return $this->foo;
    }
}
