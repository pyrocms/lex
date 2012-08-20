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
}