<?php

namespace Overtrue\CosClient\Support;

class XML
{
    /**
     * XML to array.
     *
     * @param  string  $xml  XML string
     *
     * @return array
     */
    public static function toArray(string $xml)
    {
        PHP_MAJOR_VERSION < 8 && $backup = libxml_disable_entity_loader(true);

        $xml = simplexml_load_string(
            self::sanitize($xml),
            'SimpleXMLElement',
            LIBXML_NSCLEAN | LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_NOBLANKS
        );

        $result = [
            $xml->getName() => self::objectToArray($xml),
        ];

        PHP_MAJOR_VERSION < 8 && libxml_disable_entity_loader($backup);

        return $result;
    }

    /**
     * XML encode.
     *
     * @param  mixed  $data
     *
     * @return string
     */
    public static function fromArray(array $data)
    {
        if (empty($data)) {
            return '';
        }

        $xml = new \DomDocument('1.0', 'utf-8');
        $xml->appendChild($node = self::convertToXml(\key($data), \reset($data), $xml));
        $xml->formatOutput = true;

        return $xml->saveXML();
    }

    /**
     * @param $root
     * @param  array  $data
     * @param  \DOMDocument|null  $xml
     *
     * @return \DOMElement
     */
    protected static function convertToXml($root, $data = [], ?\DOMDocument $xml = null): \DOMElement
    {
        $node = $xml->createElement($root);

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($key === '@attributes') {
                    foreach ($value as $k => $v) {
                        $node->setAttribute($k, $v);
                    }
                    continue;
                }

                if (is_array($value) && is_numeric(key($value))) {
                    foreach ($value as $k => $v) {
                        $node->appendChild(self::convertToXml($key, $v, $xml));
                    }
                } else {
                    $node->appendChild(self::convertToXml($key, $value, $xml));
                }
                unset($data[$key]);
            }
        } else {
            $node->appendChild($xml->createTextNode($data));
        }

        return $node;
    }

    protected static function objectToArray($xmlObject, array $out = [])
    {
        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = (is_object($node) || is_array($node))
                ? self::objectToArray($node)
                : $node;
        }

        return $out;
    }

    public static function removeSpace(string $xmlContents)
    {
        return trim(\preg_replace('/>\s*</', '><', $xmlContents));
    }

    /**
     * Delete invalid characters in XML.
     *
     * @see https://www.w3.org/TR/2008/REC-xml-20081126/#charsets - XML charset range
     * @see http://php.net/manual/en/regexp.reference.escape.php - escape in UTF-8 mode
     *
     * @param  string  $xml
     *
     * @return string
     */
    protected static function sanitize(string $xml)
    {
        return preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $xml);
    }
}
