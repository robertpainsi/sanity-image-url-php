<?php

use PHPUnit\Framework\TestCase;

// Include the function to test
require_once '../src/urlForImage.php';
require_once 'fixtures.php';

class UrlForImageTest extends TestCase {
	public function testShouldThrowOnInvalidSource() {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Unable to resolve image URL from source ([])' );

		urlForImage( [ 'source' => [] ] )->toString();
	}

	public function testDoesNotCropWhenNoCropIsRequired() {
		$url = urlForImage( [
			'source'    => ImageFixtures::uncroppedImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production'
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg',
			$url
		);
	}

	public function testDoesNotCropButLimitsSizeWhenOnlyWidthDimensionIsSpecified() {
		$url = urlForImage( [
			'source' => ImageFixtures::uncroppedImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'width'     => 100
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?w=100',
			$url
		);
	}

	public function testDoesNotCropButLimitsSizeWhenOnlyHeightDimensionIsSpecified() {
		$url = urlForImage( [
			'source' => ImageFixtures::uncroppedImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'height'    => 100
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?h=100',
			$url
		);
	}

	public function testATallCropIsCenteredOnTheHotspot() {
		$url = urlForImage( [
			'source' => ImageFixtures::uncroppedImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'width'     => 30,
			'height'    => 100
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?rect=150,0,900,3000&w=30&h=100',
			$url
		);
	}

	public function testAWideCropIsCenteredOnTheHotspot() {
		$url = urlForImage( [
			'source' => ImageFixtures::uncroppedImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'width'     => 100,
			'height'    => 30
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?rect=0,600,2000,600&w=100&h=30',
			$url
		);
	}

	public function testACropWithIdenticalAspectAndNoSpecifiedCropIsNotCropped() {
		$url = urlForImage( [
			'source' => ImageFixtures::uncroppedImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'width'     => 200,
			'height'    => 300
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?w=200&h=300',
			$url
		);
	}

	public function testRespectsTheCropEvenWhenNoExplicitCropIsAskedFor() {
		$url = urlForImage( [
			'source'    => ImageFixtures::croppedImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production'
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?rect=200,300,1600,2400',
			$url
		);
	}

	public function testATallCropIsCenteredOnTheHotspotAndConstrainedWithinTheImageCrop() {
		$url = urlForImage( [
			'source' => ImageFixtures::croppedImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'width'     => 30,
			'height'    => 100
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?rect=240,300,720,2400&w=30&h=100',
			$url
		);
	}

	public function testIgnoresTheImageCropIfCallerSpecifiesAnother() {
		$url = urlForImage( [
			'source' => ImageFixtures::croppedImage(),
			'rect'   => [ 'left' => 10, 'top' => 20, 'width' => 30, 'height' => 40 ],
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'width'     => 30,
			'height'    => 100
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?rect=10,20,30,40&w=30&h=100',
			$url
		);
	}

	public function testGracefullyHandlesANonHotspotImage() {
		$url = urlForImage( [
			'source' => ImageFixtures::noHotspotImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'height'    => 100
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?h=100',
			$url
		);
	}

	public function testGracefullyHandlesANonCropImage() {
		$url = urlForImage( [
			'source' => ImageFixtures::noHotspotImage(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'height'    => 100
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?h=100',
			$url
		);
	}

	public function testGracefullyHandlesMaterializedAsset() {
		$url = urlForImage( [
			'source' => ImageFixtures::materializedAssetWithCrop(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'height'    => 100
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?rect=200,300,1600,2400&h=100',
			$url
		);
	}

	public function testGracefullyHandlesRoundingErrors() {
		$url1 = urlForImage( [
			'source' => ImageFixtures::croppedPortraitImageRounding(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'width'     => 400,
			'height'    => 600
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2555x3833.jpg?w=400&h=600',
			$url1
		);

		$url2 = urlForImage( [
			'source' => ImageFixtures::croppedLandscapeImageRounding(),
			'projectId' => 'zp7mbokg',
			'dataset'   => 'production',
			'width'     => 600,
			'height'    => 400
		] );
		$this->assertEquals(
			'https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-3833x2555.jpg?w=600&h=400',
			$url2
		);
	}
}
