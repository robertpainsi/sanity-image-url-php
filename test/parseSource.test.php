<?php

use PHPUnit\Framework\TestCase;

require_once '../src/parseSource.php'; // Include the function to test

class ParseSourceTest extends TestCase {
	private function compareParsedSource( $outputSrc, $expectedSrc ) {
		$this->assertNotNull( $outputSrc );
		if ( $outputSrc === null || $expectedSrc === null ) {
			return;
		}

		$this->assertIsObject( $outputSrc );
		$this->assertIsObject( $outputSrc->asset );
		$this->assertEquals( $expectedSrc->asset->_ref, $outputSrc->asset->_ref );
		$this->assertObjectHasAttribute( 'crop', $outputSrc );
		$this->assertObjectHasAttribute( 'hotspot', $outputSrc );
	}

	public function testDoesCorrectlyParseFullImageObject() {
		$parsedSource = parseSource( imageWithNoCropSpecified() );
		$this->compareParsedSource( $parsedSource, imageWithNoCropSpecified() );
	}

	public function testDoesCorrectlyParseAssetDocumentID() {
		$parsedSource = parseSource( imageWithNoCropSpecified()->asset->_ref );
		$this->compareParsedSource( $parsedSource, imageWithNoCropSpecified() );
	}

	public function testDoesCorrectlyParseImageAssetObject() {
		$parsedSource = parseSource( imageWithNoCropSpecified()->asset );
		$this->compareParsedSource( $parsedSource, imageWithNoCropSpecified() );
	}

	public function testDoesCorrectlyParseImageAssetDocument() {
		$parsedSource = parseSource( assetDocument() );
		$this->compareParsedSource( $parsedSource, noHotspotImage() );
	}

	public function testDoesCorrectlyParseAssetObjectWithOnlyUrl() {
		$parsedSource = parseSource( assetWithUrl() );
		$this->compareParsedSource( $parsedSource, noHotspotImage() );
	}

	public function testDoesCorrectlyParseOnlyAssetUrl() {
		$parsedSource = parseSource( assetWithUrl()->asset->url );
		$this->compareParsedSource( $parsedSource, noHotspotImage() );
	}

	public function testDoesNotOverwriteCropOrHotspotSettings() {
		$this->assertEquals( parseSource( croppedImage() ), croppedImage() );
	}

	public function testDoesNotOverwriteCropOrHotspotSettingsWithMatchObject() {
		$parsedSource = parseSource( materializedAssetWithCrop() );
		$this->assertEquals( $parsedSource->asset->_ref, 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg' );
		$this->assertEquals( $parsedSource->crop->bottom, 0.1 );
	}

	public function testReturnsNullOnNonImageObject() {
		$this->assertNull( parseSource( new stdClass() ) );
	}

	public function testImageWithMaterializedAssetReadOnly() {
		$noCrop = (object) [
			'_type'   => 'image',
			'crop'    => (object) [ 'bottom' => 0, 'top' => 1, 'left' => 2, 'right' => 3 ],
			'hotspot' => (object) [ 'height' => 0.99, 'width' => 0.98, 'x' => 0.51, 'y' => 0.49 ],
			'asset'   => (object) [
				'_id'   => 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg',
				'_type' => 'sanity.imageAsset',
			]
		];

		$expectedResult = (object) [
			'_type'   => 'image',
			'crop'    => (object) [ 'bottom' => 0, 'left' => 2, 'right' => 3, 'top' => 1 ],
			'hotspot' => (object) [ 'height' => 0.99, 'width' => 0.98, 'x' => 0.51, 'y' => 0.49 ],
		];

		$this->assertEquals( parseSource( $noCrop ), $expectedResult );
	}
}
