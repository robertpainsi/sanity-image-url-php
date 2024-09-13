<?php

namespace SanityImageUrlTest;

require_once 'fixtures.php';
require_once '../src/parseSource.php';

use PHPUnit\Framework\TestCase;
use function SanityImageUrl\parseSource;

class ParseSourceTest extends TestCase {
	private function compareParsedSource( $outputSrc, $expectedSrc ) {
		$this->assertNotNull( $outputSrc );
		if ( $outputSrc === null || $expectedSrc === null ) {
			return;
		}

		$this->assertIsArray( $outputSrc );
		$this->assertIsArray( $outputSrc[ 'asset' ] );
		$this->assertEquals( $expectedSrc[ 'asset' ][ '_ref' ], $outputSrc[ 'asset' ][ '_ref' ] );
		$this->assertArrayHasKey( 'crop', $outputSrc );
		$this->assertArrayHasKey( 'hotspot', $outputSrc );
	}

	public function testDoesCorrectlyParseFullImageObject() {
		$parsedSource = parseSource( ImageFixtures::imageWithNoCropSpecified() );
		$this->compareParsedSource( $parsedSource, ImageFixtures::imageWithNoCropSpecified() );
	}

	public function testDoesCorrectlyParseAssetDocumentID() {
		$parsedSource = parseSource( ImageFixtures::imageWithNoCropSpecified()[ 'asset' ][ '_ref' ] );
		$this->compareParsedSource( $parsedSource, ImageFixtures::imageWithNoCropSpecified() );
	}

	public function testDoesCorrectlyParseImageAssetObject() {
		$parsedSource = parseSource( ImageFixtures::imageWithNoCropSpecified()[ 'asset' ] );
		$this->compareParsedSource( $parsedSource, ImageFixtures::imageWithNoCropSpecified() );
	}

	public function testDoesCorrectlyParseImageAssetDocument() {
		$parsedSource = parseSource( ImageFixtures::assetDocument() );
		$this->compareParsedSource( $parsedSource, ImageFixtures::noHotspotImage() );
	}

	public function testDoesCorrectlyParseAssetObjectWithOnlyUrl() {
		$parsedSource = parseSource( ImageFixtures::assetWithUrl() );
		$this->compareParsedSource( $parsedSource, ImageFixtures::noHotspotImage() );
	}

	public function testDoesCorrectlyParseOnlyAssetUrl() {
		$parsedSource = parseSource( ImageFixtures::assetWithUrl()[ 'asset' ][ 'url' ] );
		$this->compareParsedSource( $parsedSource, ImageFixtures::noHotspotImage() );
	}

	public function testDoesNotOverwriteCropOrHotspotSettings() {
		$this->assertEquals( parseSource( ImageFixtures::croppedImage() ), ImageFixtures::croppedImage() );
	}

	public function testDoesNotOverwriteCropOrHotspotSettingsWithMatchObject() {
		$parsedSource = parseSource( ImageFixtures::materializedAssetWithCrop() );
		$this->assertEquals( $parsedSource[ 'asset' ][ '_ref' ], 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg' );
		$this->assertEquals( $parsedSource[ 'crop' ][ 'bottom' ], 0.1 );
	}

// TODO: Fix testcase later
//	public function testReturnsNullOnNonImageObject() {
//		$this->assertNull( parseSource( new stdClass() ) );
//	}

// TODO: Fix testcase later
//	public function testImageWithMaterializedAssetReadOnly() {
//		$noCrop = [
//			'_type'   => 'image',
//			'crop'    => [ 'bottom' => 0, 'top' => 1, 'left' => 2, 'right' => 3 ],
//			'hotspot' => [ 'height' => 0.99, 'width' => 0.98, 'x' => 0.51, 'y' => 0.49 ],
//			'asset'   => [
//				'_id'   => 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg',
//				'_type' => 'sanity.imageAsset',
//			]
//		];
//
//		$expectedResult = [
//			'_type'   => 'image',
//			'crop'    => [ 'bottom' => 0, 'left' => 2, 'right' => 3, 'top' => 1 ],
//			'hotspot' => [ 'height' => 0.99, 'width' => 0.98, 'x' => 0.51, 'y' => 0.49 ],
//		];
//
//		$this->assertEquals( parseSource( $noCrop ), $expectedResult );
//	}
}
