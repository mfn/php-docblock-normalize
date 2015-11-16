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

/**
 * A doc block as found in the sources, attached with it's current namespace
 * and possible use statement aliases.
 */
class Docblock
{
    /**
     * @var string
     */
    protected $content;
    /**
     * @var string
     */
    protected $namespace;
    /**
     * @var array
     */
    protected $uses;

    /**
     * @param string $content
     * @param string $namespace
     * @param array $uses
     */
    public function __construct($content, $namespace, array $uses = [])
    {
        $this->content = $content;
        $this->namespace = $namespace;
        $this->uses = $uses;
    }

    /**
     * Before returning the doc block:
     * - types starting with a lower case character are passed through
     *   (scalar types)
     * - if an alias matches, it's full qualified symbol name will be used
     * - unleaded object types (i.e. no leading backslash) will be prefixed
     *   with the current namespace
     *
     * All object types returned are fully qualified *without* any leading backslash!
     *
     * @return string
     */
    public function getNormalizedContent()
    {
        return preg_replace_callback(
            '/( @(?:param|var)\s+)(\S+)/i',
            function ($matches) {
                $type = $matches[2];

                # Split on everything which can't be part of a class name
                $parts = preg_split('/([^a-z0-9\\\\])/i', $type, -1,
                    PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

                return $matches[1] . $this->normalizeTypeParts($parts);
            },
            $this->content
        );
    }

    /**
     * Expects the result of a preg_split with delimiter captured
     * - even offsets are symbol names
     * - odd offsets are any kind of non-symbol separating characters
     *
     * @param array $parts
     * @return string
     */
    protected function normalizeTypeParts(array $parts)
    {
        $str = '';

        for ($i = 0; $i < count($parts); $i++) {
            if ($i % 2 === 1) {
                $str .= $parts[$i];
                continue;
            }
            $str .= $this->normalizeType($parts[$i]);

        }

        return $str;
    }

    /**
     * @param string $typeDescription
     * @return string
     */
    protected function normalizeType($typeDescription)
    {
        if (strlen($typeDescription) === 0) {
            return '';
        }

        # Leading backslash means namespace absolute type; just return it
        # but without the backslash
        if ($typeDescription{0} === '\\') {
            return substr($typeDescription, 1);
        }

        # Lowercase characters are not further handler; they're expected to
        # be scalar types
        if (strtolower($typeDescription{0}) === $typeDescription{0}) {
            return $typeDescription;
        }

        # Match either the whole type or, in case of a backslash, only the part
        # before and save the rest for later concatenation
        if (!preg_match('/([^\\\\]+)(\\\\.*)?/', $typeDescription, $m)) {
            throw new \RuntimeException(
                "Error while normalizing '$typeDescription', can't determine first part"
            );
        }

        # If there's an alias, apply it
        $first = $m[1];
        if (isset($this->uses[$first])) {
            $newTypeDescription = $this->uses[$first];
            if (isset($m[2])) {
                $newTypeDescription .= $m[2];
            }
            return $newTypeDescription;
        }

        # Otherwise slap the namespace in front of it
        if (!empty($this->namespace)) {
            $typeDescription = $this->namespace . '\\' . $typeDescription;
        }

        return $typeDescription;
    }
}
