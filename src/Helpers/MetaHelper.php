<?php

namespace Nacho\Helpers;

use Symfony\Component\Yaml\Parser;

class MetaHelper
{
    private array $metaHeaders = [];
    private ?Parser $yamlParser = null;

    public function parseFileMeta($rawContent, array $headers): array
    {
        $pattern = "/^(?:\xEF\xBB\xBF)?(\/(\*)|---)[[:blank:]]*(?:\r)?\n"
            . "(?:(.*?)(?:\r)?\n)?(?(2)\*\/|---)[[:blank:]]*(?:(?:\r)?\n|$)/s";
        if (preg_match($pattern, $rawContent, $rawMetaMatches) && isset($rawMetaMatches[3])) {
            $meta = $this->getYamlParser()->parse($rawMetaMatches[3]) ?: array();
            $meta = is_array($meta) ? $meta : array('title' => $meta);
            if (intval($meta['title']) > 1000000000) {
                $meta['title'] = date('d.m.Y', intval($meta['title']));
            }

            foreach ($headers as $name => $key) {
                if (isset($meta[$name])) {
                    // rename field (e.g. remove whitespaces)
                    if ($key != $name) {
                        $meta[$key] = $meta[$name];
                        unset($meta[$name]);
                    }
                } elseif (!isset($meta[$key])) {
                    // guarantee array key existence
                    $meta[$key] = '';
                }
            }

            // workaround for issue #336
            // Symfony YAML interprets ISO-8601 datetime strings and returns timestamps instead of the string
            // this behavior conforms to the YAML standard, i.e. this is no bug of Symfony YAML
            if (!empty($meta['dateCreated'])) {
                $meta['dateCreated'] = static::prepareMetaDate($meta['dateCreated']);
            }
            if (!empty($meta['dateUpdated'])) {
                $meta['dateUpdated'] = static::prepareMetaDate($meta['dateUpdated']);
            }

            if (!empty($meta['date']) || !empty($meta['time'])) {
                if (is_int($meta['date'])) {
                    $meta['time'] = $meta['date'];
                    $meta['date'] = '';
                }

                if (empty($meta['time'])) {
                    $meta['time'] = strtotime($meta['date']) ?: '';
                } elseif (empty($meta['date'])) {
                    $rawDateFormat = (date('H:i:s', $meta['time']) === '00:00:00') ? 'Y-m-d' : 'Y-m-d H:i:s';
                    $meta['date'] = date($rawDateFormat, $meta['time']);
                }
            }
        } else {
            // guarantee array key existence
            $meta = array_fill_keys($headers, '');
        }

        return $meta;
    }

    public function getMetaHeaders(): array
    {
        if (!$this->metaHeaders) {
            $this->metaHeaders = array(
                'Title' => 'title',
                'Description' => 'description',
                'Author' => 'author',
                'DateCreated' => 'dateCreated',
                'DateUpdated' => 'dateUpdated',
                'Robots' => 'robots',
                'Hidden' => 'hidden'
            );
        }

        return $this->metaHeaders;
    }

    public static function createMetaString(array $meta): string
    {
	$meta = static::escapeMetaYaml($meta);
        return "---" . self::implode_recursive($meta, "\n") . "\n---\n";
    }

    public function getYamlParser(): Parser
    {
        if ($this->yamlParser === null) {
            $this->yamlParser = new Parser();
        }

        return $this->yamlParser;
    }

    private static function escapeMetaYaml(array $value): mixed 
    {
	if (is_array($value)) {
            // Recursively handle array values
            $escapedArray = array_map('self::escapeYamlValue', $value);
            return $escapedArray;
        } else {
            // Convert value to string if not already
            $stringValue = (string)$value;
            // Check if the value needs to be quoted
            if (preg_match('/[:\[\]{}#&*!|>\'"%@`]/', $stringValue) || is_numeric($stringValue) || in_array(strtolower($stringValue), ['true', 'false', 'null'])) {
                // Use single quotes for strings containing special characters
                // Escape single quotes inside the value
                $escapedValue = "'" . str_replace("'", "''", $stringValue) . "'";
            } else {
                // Value does not need to be quoted
                $escapedValue = $stringValue;
            }
            return $escapedValue;
        }
    }

    protected static function implode_recursive(array $arr, string $separator = '', int $depth = 0): string
    {
        $ret = '';
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $ret .= $separator . self::printDepth($depth) . $key . ': ' . self::implode_recursive($value, $separator, $depth + 1);
            } else {
                $ret .= $separator . self::printDepth($depth) . $key . ': ' . $value;
            }
        }

        return $ret;
    }

    protected static function printDepth(int $depth): string
    {
        $ret = '';
        for ($i = 0; $i < $depth; $i++) {
            $ret .= '  ';
        }
        return $ret;
    }

    private static function prepareMetaDate(string $dateToPrepare): string
    {
        $rawDateCreated = intval($dateToPrepare);
        if ($rawDateCreated !== 0) {
            return date('Y-m-d H:i:s', $rawDateCreated);
        }
        return '';
    }
}
