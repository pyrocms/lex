<?php

class ParserTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->parser = new Lex\Parser();
    }

    public function templateDataProvider()
    {
        return array(
            array(
                array(
                    'name' => 'Lex',
                    'filters' => array(
                        'enable' => true,
                    ),
                ),
            ),
        );
    }

    public function testCanSetScopeGlue()
    {
        $this->parser->scopeGlue('~');
        $scopeGlue = new ReflectionProperty($this->parser, 'scopeGlue');

        $this->assertTrue($scopeGlue->isProtected());

        $scopeGlue->setAccessible(true);
        $this->assertEquals('~', $scopeGlue->getValue($this->parser));
    }

    public function testCanGetScopeGlue()
    {
        $this->parser->scopeGlue('~');
        $this->assertEquals('~', $this->parser->scopeGlue());
    }

    public function testValueToLiteral()
    {
        $method = new ReflectionMethod($this->parser, 'valueToLiteral');

        $this->assertTrue($method->isProtected());

        $method->setAccessible(true);

        $this->assertSame("NULL", $method->invoke($this->parser, null));
        $this->assertSame("true", $method->invoke($this->parser, true));
        $this->assertSame("false", $method->invoke($this->parser, false));
        $this->assertSame("'some_string'", $method->invoke($this->parser, "some_string"));
        $this->assertSame("24", $method->invoke($this->parser, 24));
        $this->assertSame("true", $method->invoke($this->parser, array('foo')));
        $this->assertSame("false", $method->invoke($this->parser, array()));

        $mock = $this->getMock('stdClass', array('__toString'));
        $mock->expects($this->any())
             ->method('__toString')
             ->will($this->returnValue('obj_string'));

        $this->assertSame("'obj_string'", $method->invoke($this->parser, $mock));
    }

    /**
     * @dataProvider templateDataProvider
     */
    public function testGetVariable($data)
    {
        $method = new ReflectionMethod($this->parser, 'getVariable');

        $this->assertTrue($method->isProtected());

        $method->setAccessible(true);

        $this->assertEquals('Lex', $method->invoke($this->parser, 'name', $data));
        $this->assertEquals(null, $method->invoke($this->parser, 'age', $data));
        $this->assertEquals(false, $method->invoke($this->parser, 'age', $data, false));

        $this->assertEquals(true, $method->invoke($this->parser, 'filters.enable', $data));
        $this->assertEquals(null, $method->invoke($this->parser, 'filters.name', $data));
        $this->assertEquals(false, $method->invoke($this->parser, 'filters.name', $data, false));

    }

    /**
     * Regression test for https://www.pyrocms.com/forums/topics/view/19686
     */
    public function testFalseyVariableValuesParseProperly()
    {
        $data = array(
            'zero_num' => 0,
            'zero_string' => "0",
            'zero_float' => 0.0,
            'empty_string' => "",
            'null_value' => null,
            'simplexml_empty_node' => simplexml_load_string('<main></main>'),
        );

        $text = "{{zero_num}},{{zero_string}},{{zero_float}},{{empty_string}},{{null_value}},{{simplexml_empty_node}}";
        $expected = '0,0,0,,,';

        $result = $this->parser->parseVariables($text, $data);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider templateDataProvider
     */
    public function testExists($data)
    {
        $result = $this->parser->parse("{{ if exists name }}1{{ else }}0{{ endif }}", $data);
        $this->assertEquals('1', $result);

        $result = $this->parser->parse("{{ if not exists age }}0{{ else }}1{{ endif }}", $data);
        $this->assertEquals('0', $result);
    }

    /**
     * Regression test for https://github.com/fuelphp/lex/issues/2
     *
     * @dataProvider templateDataProvider
     */
    public function testUndefinedInConditional($data)
    {
        $result = $this->parser->parse("{{ if age }}0{{ else }}1{{ endif }}", $data);
        $this->assertEquals('1', $result);
    }

    /**
     * Regression test for https://github.com/pyrocms/pyrocms/issues/1906
     */
    public function testCallbacksInConditionalComparison()
    {
        $result = $this->parser->parse("{{ if foo.bar.baz == 'yes' }}Yes{{ else }}No{{ endif }}", array(), function ($name, $attributes, $content) {
            if ($name == 'foo.bar.baz') {
                return 'yes';
            }
            return 'no';
        });
        $this->assertEquals('Yes', $result);
    }

    /**
     * Regression test for https://github.com/fuelphp/lex/issues/4
     */
    public function testDoubleTagsBeingGreedy()
    {
        $result = $this->parser->parse("{{ foo.bar.baz n='1' }}/{{ foo.bar.baz n='2' }}Content{{ /foo.bar.baz }}", array(), function ($name, $attributes, $content) {
            if ($attributes['n'] == 1) {
                $this->assertEquals('', $content);
            } elseif ($attributes['n'] == 2) {
                $this->assertEquals('Content', $content);
            }
        });
        
    }
}