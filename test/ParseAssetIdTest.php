<?php

namespace SanityImageUrlTest;

use PHPUnit\Framework\TestCase;
use function SanityImageUrl\parseAssetId;

class ParseAssetIdTest extends TestCase {
	public function testThrowsOnInvalidDocumentId() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( "Malformed asset _ref 'moop'. Expected an id like \"image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg\"." );

		parseAssetId( 'moop' );
	}

	public function testThrowsOnInvalidDimensions() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( "Malformed asset _ref 'image-assetId-mooxmoo-png'. Expected an id like \"image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg\"." );

		parseAssetId( 'image-assetId-mooxmoo-png' );
	}
}
