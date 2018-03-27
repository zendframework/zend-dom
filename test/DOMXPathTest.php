<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Dom;

use DOMDocument;
use DOMNodeList;
use ErrorException;
use PHPUnit\Framework\TestCase;
use Zend\Dom\DOMXPath;

class DOMXPathTest extends TestCase
{
    /** @var DOMDocument */
    private $document;

    protected function setUp()
    {
        $this->document = new DOMDocument('<any></any>');
    }

    public function testQueryWithErrorExceptionSuccess()
    {
        $domXPath = new DOMXPath($this->document);

        $result = $domXPath->queryWithErrorException('any');

        $this->assertInstanceOf(DOMNodeList::class, $result);
    }

    public function testQueryWithErrorExceptionThrowExceptionWhenQueryExpresionIsInvalid()
    {
        $domXPath = new DOMXPath($this->document);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Invalid expression');
        $domXPath->queryWithErrorException('any#any');
    }
}
