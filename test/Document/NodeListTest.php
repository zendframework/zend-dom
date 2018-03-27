<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Dom\Document;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use PHPUnit\Framework\TestCase;
use Zend\Dom\Document\NodeList;
use Zend\Dom\Exception\BadMethodCallException;

/**
 * @covers Zend\Dom\Document\NodeList
 */
class NodeListTest extends TestCase
{
    /** @var DOMNodeList */
    protected $domNodeList;

    /** @var NodeList|DOMNode[] */
    private $nodeList;

    protected function setUp()
    {
        $document = new DOMDocument();
        $document->loadHTML('<html><body><a></a><b></b></body></html>');
        $this->domNodeList = $domNodeList = $document->getElementsByTagName('*');
        $this->nodeList = new NodeList($domNodeList);
    }

    /**
     * @group ZF-4631
     */
    public function testEmptyResultDoesNotReturnIteratorValidTrue()
    {
        $dom = new DOMDocument();
        $emptyNodeList = $dom->getElementsByTagName('a');
        $result = new NodeList($emptyNodeList);

        $this->assertFalse($result->valid());
    }

    public function testIsCountable()
    {
        $this->assertCount($this->domNodeList->length, $this->nodeList);
    }

    public function testIterable()
    {
        $extractedNodes = [];
        foreach ($this->nodeList as $key => $node) {
            $extractedNodes[$key] = $node;
        }

        $this->assertEquals(iterator_to_array($this->domNodeList), $extractedNodes);
    }

    public function testArrayHasKey()
    {
        $this->assertArrayNotHasKey(-1, $this->nodeList);
        $this->assertArrayHasKey(0, $this->nodeList);
        $this->assertArrayHasKey(1, $this->nodeList);
        $this->assertArrayHasKey(3, $this->nodeList);
        $this->assertArrayNotHasKey(4, $this->nodeList);
        $this->assertArrayNotHasKey(5, $this->nodeList);
    }

    public function testRetrieveElement()
    {
        $node = $this->nodeList[2];

        $this->assertEquals('a', $node->localName);
    }

    public function testItsNotPossibleAddElements()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempting to write to a read-only list');
        $this->nodeList[0] = '<foobar />';
    }

    public function testOffsetUnset()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempting to unset on a read-only list');
        unset($this->nodeList[0]);
    }
}
