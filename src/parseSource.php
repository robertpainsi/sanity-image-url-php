<?php

/**
 * Type-checking functions for different types of Sanity image sources.
 */

function isRef( $src ) {
	return is_array( $src ) && isset( $src[ '_ref' ] ) && is_string( $src[ '_ref' ] );
}

function isAsset( $src ) {
	return is_array( $src ) && isset( $src[ '_id' ] ) && is_string( $src[ '_id' ] );
}

function isAssetStub( $src ) {
	return is_array( $src ) && isset( $src[ 'asset' ] ) && is_array( $src[ 'asset' ] ) && isset( $src[ 'asset' ][ 'url' ] ) && is_string( $src[ 'asset' ][ 'url' ] );
}

/**
 * Convert an asset-id, asset or image to an image record suitable for processing.
 *
 * @param mixed $source The source to parse.
 *
 * @return array|null Parsed image object or null if the source is invalid.
 */
function parseSource( $source = null ) {
	if ( ! $source ) {
		return null;
	}

	$image = null;

	if ( is_string( $source ) && isUrl( $source ) ) {
		// Someone passed an existing image URL
		$image = [
			'asset' => [ '_ref' => urlToId( $source ) ],
		];
	} elseif ( is_string( $source ) ) {
		// Just an asset ID
		$image = [
			'asset' => [ '_ref' => $source ],
		];
	} elseif ( isRef( $source ) ) {
		// We just got passed an asset directly
		$image = [
			'asset' => $source,
		];
	} elseif ( isAsset( $source ) ) {
		// If we were passed an image asset document
		$image = [
			'asset' => [
				'_ref' => $source[ '_id' ] ?? '',
			],
		];
	} elseif ( isAssetStub( $source ) ) {
		// If we were passed a partial asset (`url`, but no `_id`)
		$image = [
			'asset' => [
				'_ref' => urlToId( $source[ 'asset' ][ 'url' ] ),
			],
		];
	} elseif ( isset( $source[ 'asset' ] ) && is_array( $source[ 'asset' ] ) ) {
		// Probably an actual image with materialized asset
		$image = $source;
	} else {
		// We got something that does not look like an image, or it is an image
		// that currently isn't sporting an asset.
		return null;
	}

	// Preserve crop and hotspot information if available
	if ( isset( $source[ 'crop' ] ) ) {
		$image[ 'crop' ] = $source[ 'crop' ];
	}

	if ( isset( $source[ 'hotspot' ] ) ) {
		$image[ 'hotspot' ] = $source[ 'hotspot' ];
	}

	return applyDefaults( $image );
}

/**
 * Check if the provided string is a URL.
 *
 * @param string $url The string to check.
 *
 * @return bool True if the string is a valid URL, false otherwise.
 */
function isUrl( $url ) {
	return preg_match( '/^https?:\/\//', $url ) === 1;
}

/**
 * Convert a URL to an asset ID.
 *
 * @param string $url The URL to convert.
 *
 * @return string The converted asset ID.
 */
function urlToId( $url ) {
	$parts    = explode( '/', $url );
	$lastPart = end( $parts );

	return preg_replace( '/\.([a-z]+)$/', '-$1', "image-{$lastPart}" );
}

/**
 * Apply default crop and hotspot values to the image object if they are missing.
 *
 * @param array $image The image object to modify.
 *
 * @return array The modified image object with default values applied.
 */
function applyDefaults( array $image ) {
	if ( isset( $image[ 'crop' ] ) && isset( $image[ 'hotspot' ] ) ) {
		return $image;
	}

	$result = $image;

	if ( ! isset( $result[ 'crop' ] ) ) {
		$result[ 'crop' ] = [
			'left'   => 0,
			'top'    => 0,
			'bottom' => 0,
			'right'  => 0,
		];
	}

	if ( ! isset( $result[ 'hotspot' ] ) ) {
		$result[ 'hotspot' ] = [
			'x'      => 0.5,
			'y'      => 0.5,
			'height' => 1.0,
			'width'  => 1.0,
		];
	}

	return $result;
}
