<?php

namespace FOS\ElasticaBundle\Tests\Transformer\ModelToElasticaAutoTransformer;

class POPO
{
    public $id = 123;
    public $name = 'someName';
    private $desc = 'desc';
    public $float = 7.2;
    public $bool = true;
    public $falseBool = false;
    public $date;
    public $nullValue;
    public $file;
    public $fileContents;

    public function __construct()
    {
        $this->date         = new \DateTime('1979-05-05');
        $this->file         = new \SplFileInfo(__DIR__.'/../fixtures/attachment.odt');
        $this->fileContents = file_get_contents(__DIR__.'/../fixtures/attachment.odt');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIterator()
    {
        $iterator = new \ArrayIterator();
        $iterator->append('value1');

        return $iterator;
    }

    public function getArray()
    {
        return array(
            'key1' => 'value1',
            'key2' => 'value2',
        );
    }

    public function getMultiArray()
    {
        return array(
            'key1'  => 'value1',
            'key2'  => array('value2', false, 123, 8.9, new \DateTime('1978-09-07')),
        );
    }

    public function getBool()
    {
        return $this->bool;
    }

    public function getFalseBool()
    {
        return $this->falseBool;
    }

    public function getFloat()
    {
        return $this->float;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getNullValue()
    {
        return $this->nullValue;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getFileContents()
    {
        return $this->fileContents;
    }

    public function getSub()
    {
        return array(
            (object) array('foo' => 'foo', 'bar' => 'foo', 'id' => 1),
            (object) array('foo' => 'bar', 'bar' => 'bar', 'id' => 2),
        );
    }

    public function getObj()
    {
        return array('foo' => 'foo', 'bar' => 'foo', 'id' => 1);
    }

    public function getNestedObject()
    {
        return array('key1' => (object) array('id' => 1, 'key1sub1' => 'value1sub1', 'key1sub2' => 'value1sub2'));
    }

    public function getUpper()
    {
        return (object) array('id' => 'parent', 'name' => 'a random name');
    }

    public function getUpperAlias()
    {
        return $this->getUpper();
    }

    public function getObjWithoutIdentifier()
    {
        return (object) array('foo' => 'foo', 'bar' => 'foo');
    }

    public function getSubWithoutIdentifier()
    {
        return array(
            (object) array('foo' => 'foo', 'bar' => 'foo'),
            (object) array('foo' => 'bar', 'bar' => 'bar'),
        );
    }
}
