<?php

namespace FOS\ElasticaBundle\Tests\Transformer;

class IDObject
{
    protected $id;

    /**
     * @param int|string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
