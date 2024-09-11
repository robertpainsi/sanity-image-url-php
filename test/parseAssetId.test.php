<?php

use PHPUnit\Framework\TestCase;

// Import or define your function
require_once '../src/parseAssetId.php';

class ParseAssetIdTest extends TestCase {
	public function testThrowsOnInvalidDocumentId() {
		$this->expectException( Exception::class );
		$this->expectExceptionMessageMatches( '/.*/' ); // Adjust regex if specific message matching is needed

		parseAssetId( 'moop' );
	}

	public function testThrowsOnInvalidDimensions() {
		$this->expectException( Exception::class );
		$this->expectExceptionMessageMatches( '/.*/' ); // Adjust regex if specific message matching is needed

		parseAssetId( 'image-assetId-mooxmoo-png' );
	}
}
