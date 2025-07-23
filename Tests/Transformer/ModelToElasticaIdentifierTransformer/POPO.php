<?php

namespace FOS\ElasticaBundle\Tests\Transformer\ModelToElasticaIdentifierTransformer;

class POPO
{
    protected $id = 123;
    protected $name = 'Name';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}
