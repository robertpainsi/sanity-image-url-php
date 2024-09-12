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

	/**
	 * @dataProvider casesProvider
	 */
	public function testUrls( $name, $actual, $expected ) {
		$this->assertEquals( $expected, $actual, "Failed asserting for case: $name" );
	}

	public function casesProvider() {
		$this->setUp();
		$urlFor = $this->urlFor;

		return [
			[
				'name'     => 'handles hotspot but no crop',
				'actual'   => $urlFor
					->image( [
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
						],
					] )
					->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg"
			],
			[
				'name'     => 'handles crop but no hotspot',
				'actual'   => $urlFor
					->image( [
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
						],
					] )
					->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?rect=200,300,1600,2400"
			],
			[
				'name'     => 'handles crop and hotspot being set to null (GraphQL)',
				'actual'   => $urlFor
					->image( [
						'_type'   => 'image',
						'asset'   => [
							'_ref'  => 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg',
							'_type' => 'reference',
						],
						'crop'    => null,
						'hotspot' => null,
					] )
					->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg"
			],
			[
				'name'     => 'handles materialized assets (GraphQL)',
				'actual'   => $urlFor
					->image( [
						'_type'   => 'image',
						'asset'   => [
							'_id' => 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg',
						],
						'crop'    => null,
						'hotspot' => null,
					] )
					->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg"
			],
			[
				'name'     => 'constrains aspect ratio',
				'actual'   => $urlFor->image( ImageFixtures::croppedImage() )->size( 100, 80 )->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?rect=200,300,1600,1280&w=100&h=80"
			],
			[
				'name'     => 'can be told to ignore hotspot',
				'actual'   => $urlFor->image( ImageFixtures::croppedImage() )->ignoreImageParams()->size( 100, 80 )->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?w=100&h=80"
			],
			[
				'name'     => 'toString() aliases url()',
				'actual'   => $urlFor->image( ImageFixtures::croppedImage() )->ignoreImageParams()->size( 100, 80 )->toString(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?w=100&h=80"
			],
			[
				'name'     => 'skips hotspot/crop if crop mode specified',
				'actual'   => $urlFor->image( ImageFixtures::croppedImage() )->size( 100, 80 )->crop( 'center' )->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?w=100&h=80&crop=center"
			],
			[
				'name'     => 'skips hotspot/crop if focal point specified',
				'actual'   => $urlFor->image( ImageFixtures::croppedImage() )->size( 100, 80 )->focalPoint( 10, 20 )->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?fp-x=10&fp-y=20&w=100&h=80"
			],
			[
				'name'     => 'does not crop image with no crop/hotspot specified',
				'actual'   => $urlFor->image( ImageFixtures::imageWithNoCropSpecified() )->width( 80 )->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/vK7bXJPEjVpL_C950gH1N73Zv14r7pYsbUdXl-4288x2848.jpg?w=80"
			],
			[
				'name'     => 'does crop image with no crop/hotspot specified if aspect ratio is forced',
				'actual'   => $urlFor->image( ImageFixtures::imageWithNoCropSpecified() )->width( 80 )->height( 80 )->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/vK7bXJPEjVpL_C950gH1N73Zv14r7pYsbUdXl-4288x2848.jpg?rect=720,0,2848,2848&w=80&h=80"
			],
			[
				'name'     => 'can specify options with url params',
				'actual'   => $urlFor->image( ImageFixtures::croppedImage() )->withOptions( [
					'w' => 320,
					'h' => 240
				] )->url(),
				'expected' => "https://cdn.sanity.io/images/zp7mbokg/production/Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000.jpg?rect=200,300,1600,1200&w=320&h=240"
			],
			[
				'name'     => 'flip horizontal',
				'actual'   => $this->stripPath( $urlFor->image( ImageFixtures::noHotspotImage() )->flipHorizontal()->url() ),
				'expected' => "flip=h"
			],
			[
				'name'     => 'flip vertical',
				'actual'   => $this->stripPath( $urlFor->image( ImageFixtures::noHotspotImage() )->flipVertical()->url() ),
				'expected' => "flip=v"
			],
			[
				'name'     => 'pad',
				'actual'   => $this->stripPath( $urlFor->image( ImageFixtures::noHotspotImage() )->pad( 50 )->url() ),
				'expected' => "pad=50"
			],
			[
				'name'     => 'frame = 1',
				'actual'   => $this->stripPath( $urlFor->image( ImageFixtures::noHotspotImage() )->frame( 1.0 )->url() ),
				'expected' => "frame=1"
			],
			[
				'name'     => 'automatic format',
				'actual'   => $this->stripPath( $urlFor->image( ImageFixtures::noHotspotImage() )->auto( 'format' )->url() ),
				'expected' => "auto=format"
			],
			[
				'name'     => 'dpr scaling',
				'actual'   => $this->stripPath( $urlFor->image( ImageFixtures::noHotspotImage() )->dpr( 3.0 )->url() ),
				'expected' => "dpr=3"
			],
			[
				'name'     => 'dpr scaling (noop on 1)',
				'actual'   => $this->stripPath( $urlFor->image( ImageFixtures::noHotspotImage() )->dpr( 1.0 )->url() ),
				'expected' => ""
			],
			[
				'name'     => 'sub zero top/left',
				'actual'   => $this->stripPath(
					$urlFor
						->image( 'image-928ac96d53b0c9049836c86ff25fd3c009039a16-1200x966-jpg' )
						->width( 1000 )
						->height( 805 )
						->url()
				),
				'expected' => "w=1000&h=805"
			],
			[
				'name'     => 'all hotspot/crop-compatible params',
				'actual'   => $this->stripPath(
					$urlFor
						->image( ImageFixtures::croppedImage() )
						->maxWidth( 200 )
						->minWidth( 100 )
						->maxHeight( 300 )
						->minHeight( 150 )
						->blur( 50 )
						->format( 'png' )
						->invert( true )
						->orientation( 90 )
						->quality( 50 )
						->sharpen( 7 )
						->auto( 'format' )
						->forceDownload( 'a.png' )
						->flipHorizontal()
						->flipVertical()
						->fit( 'crop' )
						->pad( 40 )
						->frame( 1 )
						->url()
				),
				'expected' => "rect=200,300,1600,2400&flip=hv&fm=png&dl=a.png&blur=50&sharp=7&invert=true&or=90&min-h=150&max-h=300&min-w=100&max-w=200&q=50&fit=crop&auto=format&pad=40&frame=1"
			],
			[
				'name'     => 'all params',
				'actual'   => $this->stripPath(
					$urlFor
						->image( ImageFixtures::croppedImage() )
						->focalPoint( 10, 20 )
						->maxWidth( 200 )
						->minWidth( 100 )
						->maxHeight( 300 )
						->minHeight( 150 )
						->blur( 50 )
						->bg( 'bf1942' )
						->rect( 10, 20, 30, 40 )
						->format( 'png' )
						->invert( true )
						->orientation( 90 )
						->quality( 50 )
						->auto( 'format' )
						->forceDownload( 'a.png' )
						->flipHorizontal()
						->flipVertical()
						->fit( 'crop' )
						->crop( 'center' )
						->pad( 40 )
						->frame( 1 )
						->url()
				),
				'expected' => "rect=10,20,30,40&bg=bf1942&fp-x=10&fp-y=20&flip=hv&fm=png&dl=a.png&blur=50&invert=true&or=90&min-h=150&max-h=300&min-w=100&max-w=200&q=50&fit=crop&crop=center&auto=format&pad=40&frame=1"
			],
		];
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
