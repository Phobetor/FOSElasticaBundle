<?php

namespace FOS\ElasticaBundle\Tests\Transformer\ModelToElasticaAutoTransformer;

use FOS\ElasticaBundle\Event\TransformEvent;
use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ModelToElasticaAutoTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformerDispatches()
    {
        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->getMock();

        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                array(
                    TransformEvent::PRE_TRANSFORM,
                    $this->isInstanceOf('FOS\ElasticaBundle\Event\TransformEvent')
                ),
                array(
                    TransformEvent::POST_TRANSFORM,
                    $this->isInstanceOf('FOS\ElasticaBundle\Event\TransformEvent')
                )
            );

        $transformer = $this->getTransformer($dispatcher);
        $transformer->transform(new POPO(), array());
    }

    public function testPropertyPath()
    {
        $transformer = $this->getTransformer();

        $document = $transformer->transform(new POPO(), array('name' => array('property_path' => false)));
        $this->assertInstanceOf('Elastica\Document', $document);
        $this->assertFalse($document->has('name'));

        $document = $transformer->transform(new POPO(), array('realName' => array('property_path' => 'name')));
        $this->assertInstanceOf('Elastica\Document', $document);
        $this->assertTrue($document->has('realName'));
        $this->assertEquals('someName', $document->get('realName'));
    }

    public function testThatCanTransformObject()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array('name' => array()));
        $data        = $document->getData();

        $this->assertInstanceOf('Elastica\Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertEquals('someName', $data['name']);
    }

    public function testThatCanTransformObjectWithCorrectTypes()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(
            new POPO(), array(
                             'name'      => array(),
                             'float'     => array(),
                             'bool'      => array(),
                             'date'      => array(),
                             'falseBool' => array(),
                        )
        );
        $data        = $document->getData();

        $this->assertInstanceOf('Elastica\Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertEquals('someName', $data['name']);
        $this->assertEquals(7.2, $data['float']);
        $this->assertEquals(true, $data['bool']);
        $this->assertEquals(false, $data['falseBool']);
        $expectedDate = new \DateTime('1979-05-05');
        $this->assertEquals($expectedDate->format('c'), $data['date']);
    }

    public function testThatCanTransformObjectWithIteratorValue()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array('iterator' => array()));
        $data        = $document->getData();

        $this->assertEquals(array('value1'), $data['iterator']);
    }

    public function testThatCanTransformObjectWithArrayValue()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array('array' => array()));
        $data        = $document->getData();

        $this->assertEquals(
            array(
                 'key1'  => 'value1',
                 'key2'  => 'value2',
            ), $data['array']
        );
    }

    public function testThatCanTransformObjectWithMultiDimensionalArrayValue()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array('multiArray' => array()));
        $data        = $document->getData();

        $expectedDate = new \DateTime('1978-09-07');

        $this->assertEquals(
            array(
                 'key1'  => 'value1',
                 'key2'  => array('value2', false, 123, 8.9, $expectedDate->format('c')),
            ), $data['multiArray']
        );
    }

    public function testThatNullValuesAreNotFilteredOut()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array('nullValue' => array()));
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('nullValue', $data));
    }

    /**
     * @expectedException Symfony\Component\PropertyAccess\Exception\RuntimeException
     */
    public function testThatCannotTransformObjectWhenGetterDoesNotExistForPrivateMethod()
    {
        $transformer = $this->getTransformer();
        $transformer->transform(new POPO(), array('desc' => array()));
    }

    public function testFileAddedForAttachmentMapping()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array('file' => array('type' => 'attachment')));
        $data        = $document->getData();

        $this->assertEquals(base64_encode(file_get_contents(__DIR__.'/../fixtures/attachment.odt')), $data['file']);
    }

    public function testFileContentsAddedForAttachmentMapping()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array('fileContents' => array('type' => 'attachment')));
        $data        = $document->getData();

        $this->assertEquals(
            base64_encode(file_get_contents(__DIR__.'/../fixtures/attachment.odt')), $data['fileContents']
        );
    }

    public function testNestedMapping()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
            'sub' => array(
                'type' => 'nested',
                'properties' => array('foo' => array()),
            ),
        ));
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('sub', $data));
        $this->assertInternalType('array', $data['sub']);
        $this->assertEquals(array(
             array('foo' => 'foo'),
             array('foo' => 'bar'),
           ), $data['sub']);
    }

    public function tesObjectMapping()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
                'sub' => array(
                    'type' => 'object',
                    'properties' => array('bar'),
                    ),
                ));
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('sub', $data));
        $this->assertInternalType('array', $data['sub']);
        $this->assertEquals(array(
             array('bar' => 'foo'),
             array('bar' => 'bar'),
           ), $data['sub']);
    }

    public function testObjectDoesNotRequireProperties()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
                'obj' => array(
                    'type' => 'object',
                    ),
                ));
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('obj', $data));
        $this->assertInternalType('array', $data['obj']);
        $this->assertEquals(array(
             'foo' => 'foo',
             'bar' => 'foo',
             'id' => 1,
       ), $data['obj']);
    }

    public function testObjectsMappingOfAtLeastOneAutoMappedObjectAndAtLeastOneManuallyMappedObject()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(
            new POPO(),
            array(
                'obj'          => array('type' => 'object', 'properties' => array()),
                'nestedObject' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'key1sub1' => array(
                            'type'       => 'string',
                            'properties' => array(),
                        ),
                        'key1sub2' => array(
                            'type'       => 'string',
                            'properties' => array(),
                        ),
                    ),
                ),
            )
        );
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('obj', $data));
        $this->assertTrue(array_key_exists('nestedObject', $data));
        $this->assertInternalType('array', $data['obj']);
        $this->assertInternalType('array', $data['nestedObject']);
        $this->assertEquals(
            array(
                'foo' => 'foo',
                'bar' => 'foo',
                'id'  => 1,
            ),
            $data['obj']
        );
        $this->assertEquals(
            array(
                'key1sub1' => 'value1sub1',
                'key1sub2' => 'value1sub2',
            ),
            $data['nestedObject'][0]
        );
    }

    public function testParentMapping()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
            '_parent' => array('type' => 'upper', 'property' => 'upper', 'identifier' => 'id'),
        ));

        $this->assertEquals('parent', $document->getParent());
    }

    public function testParentMappingWithCustomIdentifier()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
            '_parent' => array('type' => 'upper', 'property' => 'upper', 'identifier' => 'name'),
        ));

        $this->assertEquals('a random name', $document->getParent());
    }

    public function testParentMappingWithNullProperty()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
            '_parent' => array('type' => 'upper', 'property' => null, 'identifier' => 'id'),
        ));

        $this->assertEquals('parent', $document->getParent());
    }

    public function testParentMappingWithCustomProperty()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
            '_parent' => array('type' => 'upper', 'property' => 'upperAlias', 'identifier' => 'id'),
        ));

        $this->assertEquals('parent', $document->getParent());
    }

    public function testThatMappedObjectsDontNeedAnIdentifierField()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
            'objWithoutIdentifier' => array(
                'type' => 'object',
                'properties' => array(
                    'foo' => array(),
                    'bar' => array()
                )
            ),
        ));
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('objWithoutIdentifier', $data));
        $this->assertInternalType('array', $data['objWithoutIdentifier']);
        $this->assertEquals(array(
            'foo' => 'foo',
            'bar' => 'foo'
        ), $data['objWithoutIdentifier']);
    }

    public function testThatNestedObjectsDontNeedAnIdentifierField()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
            'subWithoutIdentifier' => array(
                'type' => 'nested',
                'properties' => array(
                    'foo' => array(),
                    'bar' => array()
                ),
            ),
        ));
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('subWithoutIdentifier', $data));
        $this->assertInternalType('array', $data['subWithoutIdentifier']);
        $this->assertEquals(array(
            array('foo' => 'foo', 'bar' => 'foo'),
            array('foo' => 'bar', 'bar' => 'bar'),
        ), $data['subWithoutIdentifier']);
    }

    public function testNestedTransformHandlesSingleObjects()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
            'upper' => array(
                'type' => 'nested',
                'properties' => array('name' => null)
            )
        ));

        $data = $document->getData();
        $this->assertEquals('a random name', $data['upper']['name']);
    }

    public function testNestedTransformReturnsAnEmptyArrayForNullValues()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array(
            'nullValue' => array(
                'type' => 'nested',
                'properties' => array(
                    'foo' => array(),
                    'bar' => array()
                ),
            )
        ));

        $data = $document->getData();
        $this->assertInternalType('array', $data['nullValue']);
        $this->assertEmpty($data['nullValue']);
    }

    public function testUnmappedFieldValuesAreNormalisedToStrings()
    {
        $object = new \stdClass();
        $value = new CastableObject();
        $value->foo = 'bar';

        $object->id = 123;
        $object->unmappedValue = $value;

        $transformer = $this->getTransformer();
        $document    = $transformer->transform($object, array('unmappedValue' => array('property' => 'unmappedValue')));

        $data = $document->getData();
        $this->assertEquals('bar', $data['unmappedValue']);
    }

    /**
     * @param null|\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     *
     * @return ModelToElasticaAutoTransformer
     */
    private function getTransformer($dispatcher = null)
    {
        $transformer = new ModelToElasticaAutoTransformer(array(), $dispatcher);
        $transformer->setPropertyAccessor(PropertyAccess::createPropertyAccessor());

        return $transformer;
    }
}
