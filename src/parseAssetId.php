<?php

/**
 * Parse an asset reference string and extract the id, width, height, and format.
 *
 * @param string $ref The asset reference string.
 *
 * @return array An associative array containing the parsed id, width, height, and format.
 * @throws Exception If the asset reference string is malformed.
 */
function parseAssetId( string $ref ): array {
	$example = 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg';

	// Split the reference string by '-' and extract the parts
	$parts = explode( '-', $ref );
	if ( count( $parts ) < 4 ) {
		throw new Exception( "Malformed asset _ref '{$ref}'. Expected an id like \"{$example}\"." );
	}

	// Destructure the split array
	list( , $id, $dimensionString, $format ) = $parts;

	// Validate extracted parts
	if ( ! $id || ! $dimensionString || ! $format ) {
		throw new Exception( "Malformed asset _ref '{$ref}'. Expected an id like \"{$example}\"." );
	}

	// Split dimensions by 'x'
	$dimensions = explode( 'x', $dimensionString );
	if ( count( $dimensions ) !== 2 ) {
		throw new Exception( "Malformed asset _ref '{$ref}'. Expected an id like \"{$example}\"." );
	}

	list( $imgWidthStr, $imgHeightStr ) = $dimensions;

	$width  = is_numeric( $imgWidthStr ) ? (float) $imgWidthStr : NAN;
	$height = is_numeric( $imgHeightStr ) ? (float) $imgHeightStr : NAN;

	// Check if the width and height are valid numbers
	if ( ! is_finite( $width ) || ! is_finite( $height ) ) {
		throw new Exception( "Malformed asset _ref '{$ref}'. Expected an id like \"{$example}\"." );
	}

	// Return the parsed asset data
	return [
		'id'     => $id,
		'width'  => $width,
		'height' => $height,
		'format' => $format
	];
}
