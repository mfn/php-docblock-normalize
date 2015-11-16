<?php namespace Mfn\DocblockNormalize\Tests;

/*
 * This file is part of https://github.com/mfn/php-docblock-normalize
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Markus Fischer <markus@fischer.name>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

use Mfn\DocblockNormalize\TokenParser;

class TokenParserTest extends \PHPUnit_Framework_TestCase
{

    public function testFindingDocblock()
    {
        $docblock = <<<'DOCBLOCK'
/**
 * a doc block!
 */
DOCBLOCK;

        $source = <<<SOURCE
<?php
$docblock
SOURCE;
        $docblocks = (new TokenParser())->parse(token_get_all($source));

        $this->assertCount(1, $docblocks);
        $this->assertSame($docblock, $docblocks[0]->getNormalizedContent());
    }

    public function testAddNamespaceToParam()
    {
        $source = <<<'SOURCE'
<?php namespace Foo;
/**
 * @param Bar $object
 */
SOURCE;
        $docblocks = (new TokenParser())->parse(token_get_all($source));

        $this->assertCount(1, $docblocks);
        $expectedDocblock = <<<'DOCBLOCK'
/**
 * @param Foo\Bar $object
 */
DOCBLOCK;

        $this->assertSame($expectedDocblock,
            $docblocks[0]->getNormalizedContent());
    }

    public function testReplaceAlias()
    {
        $source = <<<'SOURCE'
<?php
use Foo as Bar;
/**
 * @param Bar $object
 * @param Foo $object
 */
SOURCE;
        $docblocks = (new TokenParser())->parse(token_get_all($source));

        $this->assertCount(1, $docblocks);
        $expectedDocblock = <<<'DOCBLOCK'
/**
 * @param Foo $object
 * @param Foo $object
 */
DOCBLOCK;

        $this->assertSame($expectedDocblock,
            $docblocks[0]->getNormalizedContent());
    }

    public function testNamespaceAndAlias()
    {
        $source = <<<'SOURCE'
<?php namespace Daz;
use Foo as Bar;
/**
 * @param Bar $object
 * @param Foo $object
 * @param \Bar $object
 */
SOURCE;
        $docblocks = (new TokenParser())->parse(token_get_all($source));

        $this->assertCount(1, $docblocks);
        $expectedDocblock = <<<'DOCBLOCK'
/**
 * @param Foo $object
 * @param Daz\Foo $object
 * @param Bar $object
 */
DOCBLOCK;

        $this->assertSame($expectedDocblock,
            $docblocks[0]->getNormalizedContent());
    }


    public function testScalarTypes()
    {
        $source = <<<'SOURCE'
<?php namespace Daz;
use Foo as Bar;
/**
 * @param string $name
 * @param array $data
 */
SOURCE;
        $docblocks = (new TokenParser())->parse(token_get_all($source));

        $this->assertCount(1, $docblocks);
        $expectedDocblock = <<<'DOCBLOCK'
/**
 * @param string $name
 * @param array $data
 */
DOCBLOCK;

        $this->assertSame($expectedDocblock,
            $docblocks[0]->getNormalizedContent());
    }
}
