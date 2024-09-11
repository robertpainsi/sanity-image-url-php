<?php

use PHPUnit\Framework\TestCase;

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

		// Call the imgUrl function with the client configuration and get the image URL
		$urlBuilder = imgUrl( $client );
		$result     = $urlBuilder->image( 'image-abc123-200x200-png' )->toString();

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

		// Call the imgUrl function with the client configuration and get the image URL
		$urlBuilder = imgUrl( $client );
		$result     = $urlBuilder->image( 'image-abc123-200x200-png' )->toString();

		// Assert that the generated URL matches the expected result
		$this->assertEquals(
			'https://cdn.sanity.lol/images/abc123/foo/abc123-200x200.png',
			$result
		);
	}
}

// Helper function (assuming you have a similar function to `imgUrl` in your codebase)
function imgUrl( $client ) {
	return new ImageUrlBuilder( $client[ 'clientConfig' ] );
}

// Hypothetical ImageUrlBuilder class (you need to have a similar class or logic in your codebase)
class ImageUrlBuilder {
	private $projectId;
	private $dataset;
	private $apiHost;

	public function __construct( $config ) {
		$this->projectId = $config[ 'projectId' ];
		$this->dataset   = $config[ 'dataset' ];
		$this->apiHost   = $config[ 'apiHost' ];
	}

	public function image( $imageId ) {
		return new ImageUrl( $this->apiHost, $this->projectId, $this->dataset, $imageId );
	}
}

// Hypothetical ImageUrl class (assuming you need similar functionality)
class ImageUrl {
	private $apiHost;
	private $projectId;
	private $dataset;
	private $imageId;

	public function __construct( $apiHost, $projectId, $dataset, $imageId ) {
		$this->apiHost   = $apiHost;
		$this->projectId = $projectId;
		$this->dataset   = $dataset;
		$this->imageId   = $imageId;
	}

	public function toString() {
		// Generate the URL based on the provided parameters
		$imageIdParts = explode( '-', $this->imageId );
		array_shift( $imageIdParts ); // Remove the 'image' prefix
		$formattedImageId = implode( '-', $imageIdParts );
		$formattedImageId = preg_replace( '/-(\d+x\d+)-/', '-$1.', $formattedImageId ); // Replace last dash with dot

		return "{$this->apiHost}/images/{$this->projectId}/{$this->dataset}/{$formattedImageId}";
	}
}
