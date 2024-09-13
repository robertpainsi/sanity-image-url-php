<?php

namespace SanityImageUrl;

require_once 'urlForImage.php';

// Define valid modes as constants
$validFits      = [ 'clip', 'crop', 'fill', 'fillmax', 'max', 'scale', 'min' ];
$validCrops     = [ 'top', 'bottom', 'left', 'right', 'center', 'focalpoint', 'entropy' ];
$validAutoModes = [ 'format' ];

/**
 * Check if the client is of type SanityModernClientLike
 */
function isSanityModernClientLike( $client ): bool {
	return $client && method_exists( $client, 'config' );
}

/**
 * Check if the client is of type SanityClientLike
 */
function isSanityClientLike( $client ): bool {
	return $client && isset( $client[ 'clientConfig' ] );
}

/**
 * Rewrite the spec name
 */
function rewriteSpecName( $key ) {
	$specs = SPEC_NAME_TO_URL_NAME_MAPPINGS;
	foreach ( $specs as $entry ) {
		list( $specName, $param ) = $entry;
		if ( $key === $specName || $key === $param ) {
			return $specName;
		}
	}

	return $key;
}

/**
 * Main function to build the URL
 */
function urlBuilder( $options = null ) {
	// Did we get a modernish client?
	if ( isSanityModernClientLike( $options ) ) {
		$config  = $options->config();
		$apiHost = $config[ 'apiHost' ] ?? 'https://api.sanity.io';

		return new ImageUrlBuilder( null, [
			'baseUrl'   => str_replace( 'https://api.', 'https://cdn.', $apiHost ),
			'projectId' => $config[ 'projectId' ],
			'dataset'   => $config[ 'dataset' ]
		] );
	}

	// Did we get a SanityClient?
	if ( isSanityClientLike( $options ) ) {
		$config = $options[ 'clientConfig' ];
		$apiHost = $config[ 'apiHost' ] ?? 'https://api.sanity.io';

		return new ImageUrlBuilder( null, [
			'baseUrl'   => str_replace( 'https://api.', 'https://cdn.', $apiHost ),
			'projectId' => $config[ 'projectId' ],
			'dataset'   => $config[ 'dataset' ]
		] );
	}

	// Or just accept the options as given
	return new ImageUrlBuilder( null, $options );
}

/**
 * ImageUrlBuilder class in PHP
 */
class ImageUrlBuilder {
	public $options;

	public function __construct( $parent = null, $options = [] ) {
		$this->options = $parent ? array_merge( $parent->options ?? [], $options ?? [] ) : $options;
	}

	public function withOptions( array $options ) {
		$baseUrl = $options[ 'baseUrl' ] ?? $this->options[ 'baseUrl' ] ?? null;

		$newOptions = [ 'baseUrl' => $baseUrl ];
		foreach ( $options as $key => $value ) {
			$specKey                = rewriteSpecName( $key );
			$newOptions[ $specKey ] = $value;
		}

		return new self( $this, $newOptions );
	}

	public function image( $source ) {
		return $this->withOptions( [ 'source' => $source ] );
	}

	public function dataset( string $dataset ) {
		return $this->withOptions( [ 'dataset' => $dataset ] );
	}

	public function projectId( string $projectId ) {
		return $this->withOptions( [ 'projectId' => $projectId ] );
	}

	public function bg( string $bg ) {
		return $this->withOptions( [ 'bg' => $bg ] );
	}

	public function dpr( float $dpr ) {
		return $this->withOptions( $dpr && $dpr !== 1.0 ? [ 'dpr' => $dpr ] : [] );
	}

	public function width( float $width ) {
		return $this->withOptions( [ 'width' => $width ] );
	}

	public function height( float $height ) {
		return $this->withOptions( [ 'height' => $height ] );
	}

	public function focalPoint( float $x, float $y ) {
		return $this->withOptions( [ 'focalPoint' => [ 'x' => $x, 'y' => $y ] ] );
	}

	public function maxWidth( float $maxWidth ) {
		return $this->withOptions( [ 'maxWidth' => $maxWidth ] );
	}

	public function minWidth( float $minWidth ) {
		return $this->withOptions( [ 'minWidth' => $minWidth ] );
	}

	public function maxHeight( float $maxHeight ) {
		return $this->withOptions( [ 'maxHeight' => $maxHeight ] );
	}

	public function minHeight( float $minHeight ) {
		return $this->withOptions( [ 'minHeight' => $minHeight ] );
	}

	public function size( float $width, float $height ) {
		return $this->withOptions( [ 'width' => $width, 'height' => $height ] );
	}

	public function blur( float $blur ) {
		return $this->withOptions( [ 'blur' => $blur ] );
	}

	public function sharpen( float $sharpen ) {
		return $this->withOptions( [ 'sharpen' => $sharpen ] );
	}

	public function rect( float $left, float $top, float $width, float $height ) {
		return $this->withOptions( [
			'rect' => [
				'left'   => $left,
				'top'    => $top,
				'width'  => $width,
				'height' => $height
			]
		] );
	}

	public function format( string $format ) {
		return $this->withOptions( [ 'format' => $format ] );
	}

	public function invert( bool $invert ) {
		return $this->withOptions( [ 'invert' => ( $invert ) ? 'true' : 'false' ] );
	}

	public function orientation( float $orientation ) {
		return $this->withOptions( [ 'orientation' => $orientation ] );
	}

	public function quality( float $quality ) {
		return $this->withOptions( [ 'quality' => $quality ] );
	}

	public function forceDownload( $download ) {
		return $this->withOptions( [ 'download' => $download ] );
	}

	public function flipHorizontal() {
		return $this->withOptions( [ 'flipHorizontal' => true ] );
	}

	public function flipVertical() {
		return $this->withOptions( [ 'flipVertical' => true ] );
	}

	public function ignoreImageParams() {
		return $this->withOptions( [ 'ignoreImageParams' => true ] );
	}

	public function fit( string $value ) {
		global $validFits;
		if ( ! in_array( $value, $validFits ) ) {
			throw new \InvalidArgumentException( "Invalid fit mode \"$value\"" );
		}

		return $this->withOptions( [ 'fit' => $value ] );
	}

	public function crop( string $value ) {
		global $validCrops;
		if ( ! in_array( $value, $validCrops ) ) {
			throw new \InvalidArgumentException( "Invalid crop mode \"$value\"" );
		}

		return $this->withOptions( [ 'crop' => $value ] );
	}

	public function saturation( float $saturation ) {
		return $this->withOptions( [ 'saturation' => $saturation ] );
	}

	public function auto( string $value ) {
		global $validAutoModes;
		if ( ! in_array( $value, $validAutoModes ) ) {
			throw new \InvalidArgumentException( "Invalid auto mode \"$value\"" );
		}

		return $this->withOptions( [ 'auto' => $value ] );
	}

	public function pad( float $pad ) {
		return $this->withOptions( [ 'pad' => $pad ] );
	}

	public function frame( float $frame ) {
		if ( $frame !== 1.0 ) {
			throw new \InvalidArgumentException( "Invalid frame value \"$frame\"" );
		}

		return $this->withOptions( [ 'frame' => $frame ] );
	}

	public function url() {
		return urlForImage( $this->options );
	}

	public function toString() {
		return $this->url();
	}

	public function __toString() {
		return $this->url();
	}
}
