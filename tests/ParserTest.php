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