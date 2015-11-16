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

class TokenParser implements TokenParserInterface
{
    /**
     * @var array
     */
    protected $tokens;

    /**
     * @param array $tokens As returned by token_get_all()
     * @return Docblock[]
     */
    public function parse(array $tokens)
    {
        $this->tokens = $tokens;

        $namespace = '';
        $useAliases = [];
        $docblocks = [];

        foreach ($this->nextToken() as $token) {
            if (T_NAMESPACE === $token[0]) {
                $namespace = $this->consumeNamespaceStmt();
                $useAliases = [];
                continue;
            }

            if (T_USE === $token[0]) {
                list($fqsn, $alias) = $this->consumeUseStmt();
                $useAliases[$alias] = $fqsn;
                continue;
            }

            if (T_DOC_COMMENT === $token[0]) {
                $docblocks[] = new Docblock(
                    $token[1],
                    $namespace,
                    $useAliases
                );
                continue;
            }
        }

        return $docblocks;
    }

    /**
     * Returns the next token
     *
     * @return \Generator array
     */
    public function nextToken()
    {
        while ($this->tokens) {
            yield array_shift($this->tokens);
        }
    }

    /**
     * Consumes further tokens to return the current namespace.
     *
     * Consuming is stopped when either the ';' or '{' character are
     * encountered. The latter is an optimization and still expected to work
     * in most, but not necessarily all, cases.
     *
     * @return string
     */
    protected function consumeNamespaceStmt()
    {
        $namespace = '';

        foreach ($this->nextToken() as $token) {
            if (';' === $token || '{' === $token) {
                break;
            }

            if (T_STRING === $token[0] || T_NS_SEPARATOR === $token[0]) {
                $namespace .= $token[1];
            }
        }

        return $namespace;
    }

    /**
     * Consumes further token to return an array with
     * - the full qualified symbol name
     * - the alias
     * of the use statement.
     *
     * @return array
     */
    protected function consumeUseStmt()
    {
        $fqsn = '';
        $alias = '';
        $lastFqsnString = '';

        $state = 'parse_fqsn';

        foreach ($this->nextToken() as $token) {
            if (';' === $token) {
                break;
            }

            if (T_AS === $token[0]) {
                $state = 'parse_alias';
                continue;
            }

            switch ($state) {
                case 'parse_fqsn':
                    if (T_STRING === $token[0]) {
                        $lastFqsnString = $token[1];
                        $fqsn .= $token[1];
                    } elseif (T_NS_SEPARATOR === $token[0]) {
                        $fqsn .= $token[1];
                    }
                    break;
                case 'parse_alias':
                    if (T_STRING === $token[0]) {
                        $alias .= $token[1];
                    }
                    break;
            }
        }

        if (empty($alias)) {
            $alias = $lastFqsnString;
        }

        return [$fqsn, $alias];
    }
}
