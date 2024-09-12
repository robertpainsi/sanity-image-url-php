<?php

use PHPUnit\Framework\TestCase;

require_once '../src/builder.php';

class InitFromClientTest extends TestCase {
	public function testCanGetConfigFromClient() {
		// Mock client with configuration
		$client = [
			'clientConfig' => [
				'projectId' => 'abc123',
				'dataset'   => 'foo',
				'apiHost'   => 'https://cdn.sanity.io',
			],
		];

		$result = urlBuilder( $client )->image( 'image-abc123-200x200-png' )->toString();

		// Assert that the generated URL matches the expected result
		$this->assertEquals(
			'https://cdn.sanity.io/images/abc123/foo/abc123-200x200.png',
			$result
		);
	}

	public function testCanGetBaseUrlFromClient() {
		// Mock client with configuration
		$client = [
			'clientConfig' => [
				'apiHost'   => 'https://api.sanity.lol',
				'projectId' => 'abc123',
				'dataset'   => 'foo',
			],
		];

		$result = urlBuilder( $client )->image( 'image-abc123-200x200-png' )->toString();

		// Assert that the generated URL matches the expected result
		$this->assertEquals(
			'https://cdn.sanity.lol/images/abc123/foo/abc123-200x200.png',
			$result
		);
	}
}
