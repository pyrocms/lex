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

        $this->assertSame($method->invoke($parser, null), "NULL");
        $this->assertSame($method->invoke($parser, true), "true");
        $this->assertSame($method->invoke($parser, false), "false");
        $this->assertSame($method->invoke($parser, "some_string"), "'some_string'");
        $this->assertSame($method->invoke($parser, 24), "24");
        $this->assertSame($method->invoke($parser, array('foo')), "true");
        $this->assertSame($method->invoke($parser, array()), "false");

        $mock = $this->getMock('stdClass', array('__toString'));
        $mock->expects($this->any())
             ->method('__toString')
             ->will($this->returnValue('obj_string'));

        $this->assertSame($method->invoke($parser, $mock), "'obj_string'");
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

        $this->assertEquals($result, $expected);
    }
}