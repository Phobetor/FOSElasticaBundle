<?php

namespace FOS\ElasticaBundle\Tests\Doctrine\Listener;

class Entity
{
    private $id;
    public $identifier;

    /**
     * @param integer $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
