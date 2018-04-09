<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Dom\Document;

use PHPUnit\Framework\TestCase;
use Zend\Dom\Document\Query;

/**
 * @covers Zend\Dom\Document\Query
 */
class QueryTest extends TestCase
{
    public function testTransformShouldReturnStringByDefault()
    {
        $test = Query::cssToXpath('');
        $this->assertInternalType('string', $test);
    }

    /**
     * @group ZF-6281
     */
    public function testTransformShouldReturnMultiplePathsWhenExpressionContainsCommas()
    {
        $test = Query::cssToXpath('#foo, #bar');
        $this->assertInternalType('string', $test);
        $this->assertContains('|', $test);
        $this->assertCount(2, explode('|', $test));
    }

    public function testTransformShouldRecognizeHashSymbolAsId()
    {
        $test = Query::cssToXpath('#foo');
        $this->assertEquals("//*[@id='foo']", $test);
    }

    public function testTransformShouldRecognizeDotSymbolAsClass()
    {
        $test = Query::cssToXpath('.foo');
        $this->assertEquals("//*[contains(concat(' ', normalize-space(@class), ' '), ' foo ')]", $test);
    }

    public function testTransformShouldAssumeSpacesToIndicateRelativeXpathQueries()
    {
        $test = Query::cssToXpath('div#foo .bar');
        $this->assertContains('|', $test);
        $expected = [
            "//div[@id='foo']//*[contains(concat(' ', normalize-space(@class), ' '), ' bar ')]",
            "//div[@id='foo'][contains(concat(' ', normalize-space(@class), ' '), ' bar ')]",
        ];
        foreach ($expected as $path) {
            $this->assertContains($path, $test);
        }
    }

    public function testTransformShouldWriteChildSelectorsAsAbsoluteXpathRelations()
    {
        $test = Query::cssToXpath('div#foo>span');
        $this->assertEquals("//div[@id='foo']/span", $test);
    }

    /**
     * @group ZF-6281
     */
    public function testMultipleComplexCssSpecificationShouldTransformToExpectedXpath()
    {
        $test = Query::cssToXpath('div#foo span.bar, #bar li.baz a');
        $this->assertInternalType('string', $test);
        $this->assertContains('|', $test);
        $actual   = explode('|', $test);
        $expected = [
            "//div[@id='foo']//span[contains(concat(' ', normalize-space(@class), ' '), ' bar ')]",
            "//*[@id='bar']//li[contains(concat(' ', normalize-space(@class), ' '), ' baz ')]//a",
        ];
        $this->assertEquals(count($expected), count($actual));
        foreach ($actual as $path) {
            $this->assertContains($path, $expected);
        }
    }

    public function testClassNotationWithoutSpecifiedTagShouldResultInMultipleQueries()
    {
        $test = Query::cssToXpath('div.foo .bar a .baz span');
        $this->assertContains('|', $test);
        // @codingStandardsIgnoreStart
        $segments = [
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' foo ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' bar ')]//a//*[contains(concat(' ', normalize-space(@class), ' '), ' baz ')]//span",
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' foo ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' bar ')]//a[contains(concat(' ', normalize-space(@class), ' '), ' baz ')]//span",
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' foo ')][contains(concat(' ', normalize-space(@class), ' '), ' bar ')]//a//*[contains(concat(' ', normalize-space(@class), ' '), ' baz ')]//span",
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' foo ')][contains(concat(' ', normalize-space(@class), ' '), ' bar ')]//a[contains(concat(' ', normalize-space(@class), ' '), ' baz ')]//span",
        ];
        // @codingStandardsIgnoreEnd
        foreach ($segments as $xpath) {
            $this->assertContains($xpath, $test);
        }
    }

    public function testShouldAllowEqualitySelectionOfArbitraryAttributes()
    {
        $test = Query::cssToXpath('div[foo="bar"]');
        $this->assertEquals("//div[@foo='bar']", $test);
    }

    public function testShouldCastAttributeNamesToLowerCase()
    {
        $test = Query::cssToXpath('div[dojoType="bar"]');
        $this->assertEquals("//div[@dojotype='bar']", $test);
    }

    public function testShouldAllowContentSubSelectionOfArbitraryAttributes()
    {
        $test = Query::cssToXpath('div[foo~="bar"]');
        $this->assertEquals("//div[contains(concat(' ', normalize-space(@foo), ' '), ' bar ')]", $test);
    }

    public function testShouldAllowContentMatchingOfArbitraryAttributes()
    {
        $test = Query::cssToXpath('div[foo*="bar"]');
        $this->assertEquals("//div[contains(@foo, 'bar')]", $test);
    }

    /**
     * @group ZF-4010
     */
    public function testShouldAllowMatchingOfAttributeValues()
    {
        $test = Query::cssToXpath('tag#id @attribute');
        $this->assertEquals("//tag[@id='id']//@attribute", $test);
    }

    public function descendantSelector()
    {
        yield 'space before' => ['child >leaf'];
        yield 'space after' => ['child> leaf'];
        yield 'no spaces' => ['child>leaf'];
        yield 'with spaces' => ['child > leaf'];
        yield 'more spaces' => ['child   >   leaf'];
    }

    /**
     * @group ZF-8006
     *
     * @dataProvider descendantSelector
     *
     * @param string $path
     */
    public function testShouldAllowWhitespaceInDescendantSelectorExpressions($path)
    {
        $test = Query::cssToXpath($path);
        $this->assertEquals('//child/leaf', $test);
    }

    /**
     * @group ZF-9764
     */
    public function testIdSelectorWithAttribute()
    {
        $test = Query::cssToXpath('#id[attribute="value"]');
        $this->assertEquals("//*[@id='id'][@attribute='value']", $test);
    }

    /**
     * @group ZF-9764
     */
    public function testIdSelectorWithLeadingAsterisk()
    {
        $test = Query::cssToXpath('*#id');
        $this->assertEquals("//*[@id='id']", $test);
    }

    /**
     * @group ZF-5310
     */
    public function testCanTransformWithAttributeAndDot()
    {
        $test = Query::cssToXpath('a[href="http://example.com"]');
        $this->assertEquals("//a[@href='http://example.com']", $test);

        $test = Query::cssToXpath('a[@href="http://example.com"]');
        $this->assertEquals("//a[@href='http://example.com']", $test);
    }

    public function nestedAttributeSelectors()
    {
        return [
            'with-double-quotes' => [
                'select[name="foo"] option[selected="selected"]',
                "//select[@name='foo']//option[@selected='selected']",
            ],
            'with-single-quotes' => [
                "select[name='foo'] option[selected='selected']",
                "//select[@name='foo']//option[@selected='selected']",
            ],
            'double-quotes-containing-single-quotes' => [
                "select[name=\"f'oo\"] option[selected=\"sel'ected\"]",
                "//select[@name='f\'oo']//option[@selected='sel\'ected']",
            ],
            'single-quotes-containing-double-quotes' => [
                "select[name='f\"oo'] option[selected='sel\"ected']",
                "//select[@name='f\"oo']//option[@selected='sel\"ected']",
            ],
        ];
    }

    /**
     * @dataProvider nestedAttributeSelectors
     */
    public function testTransformNestedAttributeSelectors($selector, $expectedXpath)
    {
        $this->assertEquals($expectedXpath, Query::cssToXpath($selector));
    }

    public function testAttributeValueWithBackslash()
    {
        $test = Query::cssToXpath('select[name="\stop  \start"]');
        $this->assertEquals("//select[@name='\stop \start']", $test);
    }
}
