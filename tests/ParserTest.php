<?php

include __DIR__.'/../Lex/Parser.php';

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testCanCreateParser()
    {
        $this->assertInstanceOf('Lex\\Parser', new Lex\Parser());
    }

    public function testCanSetScopeGlue()
    {
        $parser = new Lex\Parser();
        $parser->scopeGlue('~');
        $scopeGlue = new ReflectionProperty($parser, 'scopeGlue');

        $this->assertTrue($scopeGlue->isProtected());

        $scopeGlue->setAccessible(true);
        $this->assertEquals('~', $scopeGlue->getValue($parser));
    }

    public function testCanGetScopeGlue()
    {
        $parser = new Lex\Parser();
        $parser->scopeGlue('~');
        $this->assertEquals('~', $parser->scopeGlue());
    }

    public function testValueToLiteral()
    {
        $parser = new Lex\Parser();
        $method = new ReflectionMethod($parser, 'valueToLiteral');

        $this->assertTrue($method->isProtected());

        $method->setAccessible(true);

        $this->assertSame("NULL", $method->invoke($parser, null));
        $this->assertSame("true", $method->invoke($parser, true));
        $this->assertSame("false", $method->invoke($parser, false));
        $this->assertSame("'some_string'", $method->invoke($parser, "some_string"));
        $this->assertSame("24", $method->invoke($parser, 24));
        $this->assertSame("true", $method->invoke($parser, array('foo')));
        $this->assertSame("false", $method->invoke($parser, array()));

        $mock = $this->getMock('stdClass', array('__toString'));
        $mock->expects($this->any())
             ->method('__toString')
             ->will($this->returnValue('obj_string'));

        $this->assertSame("'obj_string'", $method->invoke($parser, $mock));
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

        $parser = new Lex\Parser();

        $result = $parser->parseVariables($text, $data);

        $this->assertEquals($expected, $result);
    }
}