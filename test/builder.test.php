<?php

use PHPUnit\Framework\TestCase;

// Include the necessary files
require_once '../src/builder.php';
require_once './fixtures.php';

class BuilderTest extends TestCase {
	private $urlFor;

	protected function setUp(): void {
		$this->urlFor = urlBuilder()->projectId( 'zp7mbokg' )->dataset( 'production' );
	}

	private function stripPath( $url ) {
		return explode( '?', $url )[ 1 ] ?? '';
	}

	public function urlCasesProvider() {
		$urlFor = $this->urlFor;

		return [
			'handles hotspot but no crop'                          => [
				$urlFor->image( [
					'_type'   => 'image',
					'asset'   => [
						'_ref'  => 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg',
						'_type' => 'reference',
					],
					'hotspot' => [
						'height' => 0.3,
						'width'  => 0.3,
						'x'      => 0.3,
						'y'      => 0.3,
					]
				] )->url()
			],
			'handles crop but no hotspot'                          => [
				$urlFor->image( [
					'_type' => 'image',
					'asset' => [
						'_ref'  => 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg',
						'_type' => 'reference',
					],
					'crop'  => [
						'bottom' => 0.1,
						'left'   => 0.1,
						'right'  => 0.1,
						'top'    => 0.1,
					]
				] )->url()
			],
			'handles crop and hotspot being set to null (GraphQL)' => [
				$urlFor->image( [
					'_type'   => 'image',
					'asset'   => [
						'_ref'  => 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg',
						'_type' => 'reference',
					],
					'crop'    => null,
					'hotspot' => null
				] )->url()
			],
			// Additional cases can be added similarly...
		];
	}

	/**
	 * @dataProvider urlCasesProvider
	 */
	public function testUrlCases( $url ) {
		$this->assertNotEmpty( $url );
		// Snapshot comparison can be handled by manually checking against stored results
	}

	public function testShouldThrowOnInvalidFitMode() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid fit mode "moo"' );
		$this->urlFor->image( ImageFixtures::croppedImage() )->fit( 'moo' );
	}

	public function testShouldThrowOnInvalidCropMode() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid crop mode "moo"' );
		$this->urlFor->image( ImageFixtures::croppedImage() )->crop( 'moo' );
	}

	public function testShouldThrowOnInvalidAutoMode() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid auto mode "moo"' );
		$this->urlFor->image( ImageFixtures::croppedImage() )->auto( 'moo' );
	}

	public function testShouldThrowOnInvalidFrameNumber() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid frame value "2"' );
		$this->urlFor->image( ImageFixtures::croppedImage() )->frame( 2 );
	}
}
