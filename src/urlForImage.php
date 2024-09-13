<?php

namespace SanityImageUrl;

require_once 'parseAssetId.php';
require_once 'parseSource.php';

define( 'SPEC_NAME_TO_URL_NAME_MAPPINGS', [
	[ 'width', 'w' ],
	[ 'height', 'h' ],
	[ 'format', 'fm' ],
	[ 'download', 'dl' ],
	[ 'blur', 'blur' ],
	[ 'sharpen', 'sharp' ],
	[ 'invert', 'invert' ],
	[ 'orientation', 'or' ],
	[ 'minHeight', 'min-h' ],
	[ 'maxHeight', 'max-h' ],
	[ 'minWidth', 'min-w' ],
	[ 'maxWidth', 'max-w' ],
	[ 'quality', 'q' ],
	[ 'fit', 'fit' ],
	[ 'crop', 'crop' ],
	[ 'saturation', 'sat' ],
	[ 'auto', 'auto' ],
	[ 'dpr', 'dpr' ],
	[ 'pad', 'pad' ],
	[ 'frame', 'frame' ]
] );

function urlForImage( $options ) {
	$spec   = array_merge( [], (array) $options );
	$source = $spec[ 'source' ] ?? null;
	unset( $spec[ 'source' ] );

	$image = parseSource( $source );
	if ( ! $image ) {
		throw new \Exception( "Unable to resolve image URL from source (" . json_encode( $source ) . ")" );
	}

	$id    = $image[ 'asset' ][ '_ref' ] ?? $image[ 'asset' ][ '_id' ] ?? '';
	$asset = parseAssetId( $id );

	$cropLeft = round( $image[ 'crop' ][ 'left' ] * $asset[ 'width' ] );
	$cropTop  = round( $image[ 'crop' ][ 'top' ] * $asset[ 'height' ] );
	$crop     = [
		'left'   => $cropLeft,
		'top'    => $cropTop,
		'width'  => round( $asset[ 'width' ] - $image[ 'crop' ][ 'right' ] * $asset[ 'width' ] - $cropLeft ),
		'height' => round( $asset[ 'height' ] - $image[ 'crop' ][ 'bottom' ] * $asset[ 'height' ] - $cropTop ),
	];

	$hotSpotVerticalRadius   = ( $image[ 'hotspot' ][ 'height' ] * $asset[ 'height' ] ) / 2;
	$hotSpotHorizontalRadius = ( $image[ 'hotspot' ][ 'width' ] * $asset[ 'width' ] ) / 2;
	$hotSpotCenterX          = $image[ 'hotspot' ][ 'x' ] * $asset[ 'width' ];
	$hotSpotCenterY          = $image[ 'hotspot' ][ 'y' ] * $asset[ 'height' ];
	$hotspot                 = [
		'left'   => $hotSpotCenterX - $hotSpotHorizontalRadius,
		'top'    => $hotSpotCenterY - $hotSpotVerticalRadius,
		'right'  => $hotSpotCenterX + $hotSpotHorizontalRadius,
		'bottom' => $hotSpotCenterY + $hotSpotVerticalRadius,
	];

	if ( ! ( $spec[ 'rect' ] ?? null ) && ! ( $spec[ 'focalPoint' ] ?? null ) && ! ( $spec[ 'ignoreImageParams' ] ?? null ) && ! ( $spec[ 'crop' ] ?? null ) ) {
		$spec = array_merge( $spec, fit( [ 'crop' => $crop, 'hotspot' => $hotspot ], $spec ) );
	}

	return specToImageUrl( array_merge( $spec, [ 'asset' => $asset ] ) );
}

function specToImageUrl( array $spec ) {
	$cdnUrl   = rtrim( $spec[ 'baseUrl' ] ?? 'https://cdn.sanity.io', '/' );
	$filename = "{$spec['asset']['id']}-{$spec['asset']['width']}x{$spec['asset']['height']}.{$spec['asset']['format']}";
	$baseUrl  = "{$cdnUrl}/images/{$spec['projectId']}/{$spec['dataset']}/{$filename}";

	$params = [];

	if ( ! empty( $spec[ 'rect' ] ) ) {
		$rect            = $spec[ 'rect' ];
		$isEffectiveCrop = $rect[ 'left' ] !== 0.0
		                   || $rect[ 'top' ] !== 0.0
		                   || $rect[ 'height' ] !== $spec[ 'asset' ][ 'height' ]
		                   || $rect[ 'width' ] !== $spec[ 'asset' ][ 'width' ];

		if ( $isEffectiveCrop ) {
			$params[] = "rect={$rect['left']},{$rect['top']},{$rect['width']},{$rect['height']}";
		}
	}

	if ( isset( $spec[ 'bg' ] ) ) {
		$params[] = "bg=" . $spec[ 'bg' ];
	}

	if ( isset( $spec[ 'focalPoint' ] ) ) {
		$params[] = "fp-x=" . $spec[ 'focalPoint' ][ 'x' ];
		$params[] = "fp-y=" . $spec[ 'focalPoint' ][ 'y' ];
	}

	$flip = implode( '', array_filter( [
		isset( $spec[ 'flipHorizontal' ] ) ? 'h' : '',
		isset( $spec[ 'flipVertical' ] ) ? 'v' : ''
	] ) );
	if ( $flip ) {
		$params[] = "flip={$flip}";
	}

	foreach ( SPEC_NAME_TO_URL_NAME_MAPPINGS as $mapping ) {
		[ $specName, $param ] = $mapping;
		if ( isset( $spec[ $specName ] ) ) {
			$params[] = "{$param}=" . urlencode( $spec[ $specName ] );
		} elseif ( isset( $spec[ $param ] ) ) {
			$params[] = "{$param}=" . urlencode( $spec[ $param ] );
		}
	}

	if ( count( $params ) === 0 ) {
		return $baseUrl;
	}

	return $baseUrl . '?' . implode( '&', $params );
}

function fit( $source, $spec ) {
	$cropRect = [];

	$imgWidth  = $spec[ 'width' ] ?? null;
	$imgHeight = $spec[ 'height' ] ?? null;

	if ( ! $imgWidth || ! $imgHeight ) {
		return [ 'width' => $imgWidth, 'height' => $imgHeight, 'rect' => $source[ 'crop' ] ];
	}

	$crop               = $source[ 'crop' ];
	$hotspot            = $source[ 'hotspot' ];
	$desiredAspectRatio = $imgWidth / $imgHeight;
	$cropAspectRatio    = $crop[ 'width' ] / $crop[ 'height' ];

	if ( $cropAspectRatio > $desiredAspectRatio ) {
		$height = round( $crop[ 'height' ] );
		$width  = round( $height * $desiredAspectRatio );
		$top    = max( 0.0, round( $crop[ 'top' ] ) );

		$hotspotXCenter = round( ( $hotspot[ 'right' ] - $hotspot[ 'left' ] ) / 2 + $hotspot[ 'left' ] );
		$left           = max( 0.0, round( $hotspotXCenter - $width / 2 ) );

		if ( $left < $crop[ 'left' ] ) {
			$left = $crop[ 'left' ];
		} elseif ( $left + $width > $crop[ 'left' ] + $crop[ 'width' ] ) {
			$left = $crop[ 'left' ] + $crop[ 'width' ] - $width;
		}

		$cropRect = [ 'left' => $left, 'top' => $top, 'width' => $width, 'height' => $height ];
	} else {
		$width  = $crop[ 'width' ];
		$height = round( $width / $desiredAspectRatio );
		$left   = max( 0.0, round( $crop[ 'left' ] ) );

		$hotspotYCenter = round( ( $hotspot[ 'bottom' ] - $hotspot[ 'top' ] ) / 2 + $hotspot[ 'top' ] );
		$top            = max( 0.0, round( $hotspotYCenter - $height / 2 ) );

		if ( $top < $crop[ 'top' ] ) {
			$top = $crop[ 'top' ];
		} elseif ( $top + $height > $crop[ 'top' ] + $crop[ 'height' ] ) {
			$top = $crop[ 'top' ] + $crop[ 'height' ] - $height;
		}

		$cropRect = [ 'left' => $left, 'top' => $top, 'width' => $width, 'height' => $height ];
	}

	return [
		'width'  => $imgWidth,
		'height' => $imgHeight,
		'rect'   => $cropRect,
	];
}

?>
