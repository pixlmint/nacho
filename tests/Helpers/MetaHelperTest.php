<?php

namespace Tests\Helpers;

use Nacho\Helpers\MetaHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Parser;

class MetaHelperTest extends TestCase
{
    private MetaHelper $metaHelper;
    private Parser $yamlParser;

    protected function setUp(): void
    {
        $this->yamlParser = new Parser();
        $this->metaHelper = new MetaHelper();
    }

    public function testParseFileMetaWithValidContent()
    {
        $rawContent = "---\ntitle: Test Title\nauthor: Test Author\n---";
        $headers = ['title' => 'title', 'author' => 'author'];
        $expected = ['title' => 'Test Title', 'author' => 'Test Author'];

        $this->assertEquals($expected, $this->metaHelper->parseFileMeta($rawContent, $headers));
    }

    public function testParseFileMetaWithInvalidContent()
    {
        $rawContent = "Invalid Content";
        $headers = ['title' => 'title', 'author' => 'author'];
        $expected = ['title' => '', 'author' => ''];

        $this->assertEquals($expected, $this->metaHelper->parseFileMeta($rawContent, $headers));
    }

    public function testGetMetaHeadersReturnsDefaultHeaders()
    {
        $expected = [
            'Title' => 'title',
            'Description' => 'description',
            'Author' => 'author',
            'DateCreated' => 'dateCreated',
            'DateUpdated' => 'dateUpdated',
            'Robots' => 'robots',
            'Hidden' => 'hidden'
        ];

        $this->assertEquals($expected, $this->metaHelper->getMetaHeaders());
    }

    public function testCreateMetaStringFromValidArray()
    {
        $meta = [
            'title' => 'Test Title',
            'author' => 'Test Author'
        ];
        $expected = "---\ntitle: Test Title\nauthor: Test Author\n---\n";

        $this->assertEquals($expected, MetaHelper::createMetaString($meta));
    }

    public function testCreateMetaStringFromNestedArray()
    {
        $meta = [
            'title' => 'Test Title',
            'details' => [
                'author' => 'Test Author',
                'year' => '2023'
            ]
        ];
        $expected = "---\ntitle: Test Title\ndetails: \n  author: Test Author\n  year: '2023'\n---\n";

        $this->assertEquals($expected, MetaHelper::createMetaString($meta));
    }

    public function testCreateMetaStringFromEmptyArray()
    {
        $meta = [];
        $expected = "---\n---\n";

        $this->assertEquals($expected, MetaHelper::createMetaString($meta));
    }
}
