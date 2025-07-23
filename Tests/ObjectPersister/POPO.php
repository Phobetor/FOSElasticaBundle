<?php

namespace FOS\ElasticaBundle\Tests\ObjectPersister;

class POPO
{
    public $id   = 123;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return 'popoName';
    }
}
