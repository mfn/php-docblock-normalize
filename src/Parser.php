<?php namespace Mfn\DocblockNormalize;

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

class Parser
{
    /**
     * @var TokenParserInterface
     */
    protected $parser;

    public function __construct(TokenParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param string $filename
     * @return Docblock[]
     */
    public function parseFile($filename)
    {
        return $this->parseString(file_get_contents($filename));
    }

    /**
     * @param string $string
     * @return Docblock[]
     */
    public function parseString($string)
    {
        return $this->parseTokens(token_get_all($string));
    }

    /**
     * @param array $tokens
     * @return Docblock[]
     */
    public function parseTokens(array $tokens)
    {
        return $this->parser->parse($tokens);
    }
}
