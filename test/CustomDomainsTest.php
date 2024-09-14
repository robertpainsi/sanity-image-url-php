<?php

namespace SanityImageUrlTest;

use PHPUnit\Framework\TestCase;
use function SanityImageUrl\urlBuilder;

class CustomDomainsTest extends TestCase {
	public function testCanSpecifyBaseUrl() {
		$options = [
			'projectId' => 'xyz321',
			'dataset'   => 'staging',
			'baseUrl'   => 'https://mycustom.domain',
		];

		$url = (string) urlBuilder( $options )->image( 'image-928ac96d53b0c9049836c86ff25fd3c009039a16-200x200-png' );
		$this->assertEquals(
			'https://mycustom.domain/images/xyz321/staging/928ac96d53b0c9049836c86ff25fd3c009039a16-200x200.png',
			$url
		);
	}

	public function testStripsTrailingSlashesForBaseUrl() {
		$options = [
			'projectId' => 'xyz321',
			'dataset'   => 'staging',
			'baseUrl'   => 'https://mycustom.domain/',
		];

		$url = urlBuilder( $options )->image( 'image-928ac96d53b0c9049836c86ff25fd3c009039a16-200x200-png' );
		$this->assertEquals(
			'https://mycustom.domain/images/xyz321/staging/928ac96d53b0c9049836c86ff25fd3c009039a16-200x200.png',
			$url
		);
	}

	public function testCanInferFromClientApiHost() {
		$options = [
			'clientConfig' => [
				'projectId' => 'xyz321',
				'dataset'   => 'staging',
				'apiHost'   => 'https://api.totally.custom',
			],
		];

		$url = urlBuilder( $options )->image( 'image-928ac96d53b0c9049836c86ff25fd3c009039a16-200x200-png' );
		$this->assertEquals(
			'https://cdn.totally.custom/images/xyz321/staging/928ac96d53b0c9049836c86ff25fd3c009039a16-200x200.png',
			$url
		);
	}
}
