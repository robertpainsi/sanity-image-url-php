<?php
/**
 * @link https://github.com/sanity-io/image-url/blob/main/src/urlForImage.ts
 */

namespace SanityImageUrl;

const SPEC_NAME_TO_URL_NAME_MAPPINGS = [
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
];

function urlForImage( array $options = [] ): string {
	$spec   = $options;
	$source = $spec[ 'source' ] ?? null;
	unset( $spec[ 'source' ] );

	$image = parseSource( $source );
	if ( ! $image ) {
		throw new \Exception( "Unable to resolve image URL from source (" . json_encode( $source ) . ")" );
	}

	$id    = $image[ 'asset' ][ '_ref' ] ?? $image[ 'asset' ][ '_id' ] ?? '';
	$asset = parseAssetId( $id );

	// Compute crop rect in terms of pixel coordinates in the raw source image
	$cropLeft = round( $image[ 'crop' ][ 'left' ] * $asset[ 'width' ] );
	$cropTop  = round( $image[ 'crop' ][ 'top' ] * $asset[ 'height' ] );
	$crop     = [
		'left'   => $cropLeft,
		'top'    => $cropTop,
		'width'  => round( $asset[ 'width' ] - $image[ 'crop' ][ 'right' ] * $asset[ 'width' ] - $cropLeft ),
		'height' => round( $asset[ 'height' ] - $image[ 'crop' ][ 'bottom' ] * $asset[ 'height' ] - $cropTop ),
	];

	// Compute hot spot rect in terms of pixel coordinates
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

	// If irrelevant, or if we are requested to: don't perform crop/fit based on
	// the crop/hotspot.
	if ( ! ( $spec[ 'rect' ] ?? $spec[ 'focalPoint' ] ?? $spec[ 'ignoreImageParams' ] ?? $spec[ 'crop' ] ?? false ) ) {
		$spec = array_merge( $spec, fit( [ 'crop' => $crop, 'hotspot' => $hotspot ], $spec ) );
	}

	return specToImageUrl( array_merge( $spec, [ 'asset' => $asset ] ) );
}

function specToImageUrl( array $spec ): string {
	$cdnUrl   = preg_replace( "/\/+$/", '', $spec[ 'baseUrl' ] ?? 'https://cdn.sanity.io' );
	$filename = "{$spec['asset']['id']}-{$spec['asset']['width']}x{$spec['asset']['height']}.{$spec['asset']['format']}";
	$baseUrl  = "{$cdnUrl}/images/{$spec['projectId']}/{$spec['dataset']}/{$filename}";

	$params = [];

	if ( isset( $spec[ 'rect' ] ) ) {
		// Only bother url with a crop if it actually crops anything
		[ 'left' => $left, 'top' => $top, 'width' => $width, 'height' => $height ] = $spec[ 'rect' ];
		$isEffectiveCrop =
			$left != 0 || $top != 0 || $height != $spec[ 'asset' ][ 'height' ] || $width != $spec[ 'asset' ][ 'width' ];

		if ( $isEffectiveCrop ) {
			$params[] = "rect=$left,$top,$width,$height";
		}
	}

	if ( isset( $spec[ 'bg' ] ) ) {
		$params[] = "bg={$spec[ 'bg' ]}";
	}

	if ( isset( $spec[ 'focalPoint' ] ) ) {
		$params[] = "fp-x={$spec[ 'focalPoint' ][ 'x' ]}";
		$params[] = "fp-y={$spec[ 'focalPoint' ][ 'y' ]}";
	}

	$flip = implode( '', array_filter( [
		isset( $spec[ 'flipHorizontal' ] ) ? 'h' : false,
		isset( $spec[ 'flipVertical' ] ) ? 'v' : false
	] ) );
	if ( $flip ) {
		$params[] = "flip=$flip";
	}

	// Map from spec name to url param name, and allow using the actual param name as an alternative
	foreach ( SPEC_NAME_TO_URL_NAME_MAPPINGS as $mapping ) {
		[ $specName, $param ] = $mapping;
		if ( isset( $spec[ $specName ] ) ) {
			$params[] = "$param=" . urlencode( $spec[ $specName ] );
		} elseif ( isset( $spec[ $param ] ) ) {
			$params[] = "$param=" . urlencode( $spec[ $param ] );
		}
	}

	if ( count( $params ) === 0 ) {
		return $baseUrl;
	}

	return $baseUrl . '?' . implode( '&', $params );
}

function fit( array $source, array $spec ): array {
	$imgWidth  = $spec[ 'width' ] ?? null;
	$imgHeight = $spec[ 'height' ] ?? null;

	// If we are not constraining the aspect ratio, we'll just use the whole crop
	if ( ! ( $imgWidth && $imgHeight ) ) {
		return [ 'width' => $imgWidth, 'height' => $imgHeight, 'rect' => $source[ 'crop' ] ];
	}

	$crop    = $source[ 'crop' ];
	$hotspot = $source[ 'hotspot' ];

	// If we are here, that means aspect ratio is locked and fitting will be a bit harder
	$desiredAspectRatio = $imgWidth / $imgHeight;
	$cropAspectRatio    = $crop[ 'width' ] / $crop[ 'height' ];

	if ( $cropAspectRatio > $desiredAspectRatio ) {
		// The crop is wider than the desired aspect ratio. That means we are cutting from the sides
		$height = round( $crop[ 'height' ] );
		$width  = round( $height * $desiredAspectRatio );
		$top    = max( 0.0, round( $crop[ 'top' ] ) );

		// Center output horizontally over hotspot
		$hotspotXCenter = round( ( $hotspot[ 'right' ] - $hotspot[ 'left' ] ) / 2 + $hotspot[ 'left' ] );
		$left           = max( 0.0, round( $hotspotXCenter - $width / 2 ) );

		// Keep output within crop
		if ( $left < $crop[ 'left' ] ) {
			$left = $crop[ 'left' ];
		} elseif ( $left + $width > $crop[ 'left' ] + $crop[ 'width' ] ) {
			$left = $crop[ 'left' ] + $crop[ 'width' ] - $width;
		}

		$cropRect = [ 'left' => $left, 'top' => $top, 'width' => $width, 'height' => $height ];
	} else {
		// The crop is taller than the desired ratio, we are cutting from top and bottom
		$width  = $crop[ 'width' ];
		$height = round( $width / $desiredAspectRatio );
		$left   = max( 0.0, round( $crop[ 'left' ] ) );

		// Center output vertically over hotspot
		$hotspotYCenter = round( ( $hotspot[ 'bottom' ] - $hotspot[ 'top' ] ) / 2 + $hotspot[ 'top' ] );
		$top            = max( 0.0, round( $hotspotYCenter - $height / 2 ) );

		// Keep output rect within crop
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
