<?php

namespace FOS\ElasticaBundle\Tests\Transformer;

class POPO
{
    public $id;
    public $data;

    /**
     * @param mixed $id
     */
    public function __construct($id, $data)
    {
        $this->data = $data;
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
