<?php

namespace SanityImageUrlTest;

use PHPUnit\Framework\TestCase;

use function SanityImageUrl\urlBuilder;

class InitFromClientTest extends TestCase
{
    public function testCanGetConfigFromClient()
    {
        $client = [
            'clientConfig' => [
                'projectId' => 'abc123',
                'dataset' => 'foo',
                'apiHost' => 'https://cdn.sanity.io',
            ],
        ];

        $result = urlBuilder($client)->image('image-abc123-200x200-png');

        $this->assertEquals(
            'https://cdn.sanity.io/images/abc123/foo/abc123-200x200.png',
            $result
        );
    }

    public function testCanGetBaseUrlFromClient()
    {
        $client = [
            'clientConfig' => [
                'apiHost' => 'https://api.sanity.lol',
                'projectId' => 'abc123',
                'dataset' => 'foo',
            ],
        ];

        $result = urlBuilder($client)->image('image-abc123-200x200-png');

        $this->assertEquals(
            'https://cdn.sanity.lol/images/abc123/foo/abc123-200x200.png',
            $result
        );
    }
}
