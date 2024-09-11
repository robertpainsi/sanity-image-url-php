<?php

use PHPUnit\Framework\TestCase;

// Include the necessary files
require_once '../src/imgUrl.php';  // Assuming imgUrl function is defined here

class CustomDomainsTest extends TestCase {
	public function testCanSpecifyBaseUrl() {
		$options = [
			'projectId' => 'xyz321',
			'dataset'   => 'staging',
			'baseUrl'   => 'https://mycustom.domain',
		];

		$url = imgUrl( $options )->image( 'image-928ac96d53b0c9049836c86ff25fd3c009039a16-200x200-png' )->toString();
		$this->assertEquals(
			'https://mycustom.domain/images/xyz321/staging/928ac96d53b0c9049836c86ff25fd3c009039a16-200x200.png',
			$url
		);
	}

	public function testStripsTrailingSlashesForBaseUrl() {
		$options = [
			'projectId' => 'xyz321',
			'dataset'   => 'staging',
			'baseUrl'   => 'https://mycustom.domain/', // Trailing slash in baseUrl
		];

		$url = imgUrl( $options )->image( 'image-928ac96d53b0c9049836c86ff25fd3c009039a16-200x200-png' )->toString();
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

		$url = imgUrl( $options )->image( 'image-928ac96d53b0c9049836c86ff25fd3c009039a16-200x200-png' )->toString();
		$this->assertEquals(
			'https://cdn.totally.custom/images/xyz321/staging/928ac96d53b0c9049836c86ff25fd3c009039a16-200x200.png',
			$url
		);
	}
}
