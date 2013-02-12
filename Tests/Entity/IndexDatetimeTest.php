<?php
namespace Oro\Bundle\SearchBundle\Test\Entity;

use Oro\Bundle\SearchBundle\Entity\IndexDatetime;

class IndexDatetimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Entity\IndexDatetime
     */
    private $index;

    public function setUp()
    {
        $this->index = new IndexDatetime();
    }

    public function testField()
    {
        $this->assertNull($this->index->getField());
        $this->index->setField('test_datetime_field');
        $this->assertEquals('test_datetime_field', $this->index->getField());
    }

    public function testValue()
    {
        $this->assertNull($this->index->getValue());
        $this->index->setValue(new \Datetime('2012-12-12'));
        $this->assertEquals('2012-12-12', $this->index->getValue()->format('Y-m-d'));
    }
}
