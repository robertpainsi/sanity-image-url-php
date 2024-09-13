<?php

namespace SanityImageUrl;

function isRef( $src ) {
	return is_array( $src ) && isset( $src[ '_ref' ] ) && is_string( $src[ '_ref' ] );
}

function isAsset( $src ) {
	return is_array( $src ) && isset( $src[ '_id' ] ) && is_string( $src[ '_id' ] );
}

function isAssetStub( $src ) {
	return is_array( $src ) && isset( $src[ 'asset' ] ) && is_array( $src[ 'asset' ] ) && isset( $src[ 'asset' ][ 'url' ] ) && is_string( $src[ 'asset' ][ 'url' ] );
}

function parseSource( $source = null ) {
	if ( ! $source ) {
		return null;
	}

	$image = null;

	if ( is_string( $source ) && isUrl( $source ) ) {
		$image = [
			'asset' => [ '_ref' => urlToId( $source ) ],
		];
	} elseif ( is_string( $source ) ) {
		$image = [
			'asset' => [ '_ref' => $source ],
		];
	} elseif ( isRef( $source ) ) {
		$image = [
			'asset' => $source,
		];
	} elseif ( isAsset( $source ) ) {
		$image = [
			'asset' => [
				'_ref' => $source[ '_id' ] ?? '',
			],
		];
	} elseif ( isAssetStub( $source ) ) {
		$image = [
			'asset' => [
				'_ref' => urlToId( $source[ 'asset' ][ 'url' ] ),
			],
		];
	} elseif ( isset( $source[ 'asset' ] ) && is_array( $source[ 'asset' ] ) ) {
		$image = $source;
	} else {
		return null;
	}

	if ( isset( $source[ 'crop' ] ) ) {
		$image[ 'crop' ] = $source[ 'crop' ];
	}

	if ( isset( $source[ 'hotspot' ] ) ) {
		$image[ 'hotspot' ] = $source[ 'hotspot' ];
	}

	return applyDefaults( $image );
}

function isUrl( $url ) {
	return preg_match( '/^https?:\/\//', $url ) === 1;
}

function urlToId( $url ) {
	$parts    = explode( '/', $url );
	$lastPart = end( $parts );

	return preg_replace( '/\.([a-z]+)$/', '-$1', "image-{$lastPart}" );
}

function applyDefaults( array $image ) {
	if ( isset( $image[ 'crop' ] ) && isset( $image[ 'hotspot' ] ) ) {
		return $image;
	}

	$result = $image;

	if ( ! isset( $result[ 'crop' ] ) ) {
		$result[ 'crop' ] = [
			'left'   => 0.0,
			'top'    => 0.0,
			'bottom' => 0.0,
			'right'  => 0.0,
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
