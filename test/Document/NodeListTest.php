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
use Zend\Dom\Document\NodeList;

/**
 * @covers Zend\Dom\Document\NodeList
 */
class NodeListTest extends \PHPUnit_Framework_TestCase
{
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
}
