<?php

namespace SanityImageUrl;

const VALID_FITS       = [ 'clip', 'crop', 'fill', 'fillmax', 'max', 'scale', 'min' ];
const VALID_CROPS      = [ 'top', 'bottom', 'left', 'right', 'center', 'focalpoint', 'entropy' ];
const VALID_AUTO_MODES = [ 'format' ];

function isSanityModernClientLike( $client ): bool {
	return $client && is_object( $client ) && method_exists( $client, 'config' );
}

function isSanityClientLike( $client ): bool {
	return $client && is_array( $client ) && isset( $client[ 'clientConfig' ] );
}

function rewriteSpecName( string $key ): string {
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
 * @param array|object|null $options
 *
 * @return ImageUrlBuilder
 */
function urlBuilder( $options = null ): ImageUrlBuilder {
	if ( isSanityModernClientLike( $options ) ) {
		[ 'apiHost' => $apiHost, 'projectId' => $projectId, 'dataset' => $dataset ] = $options->config() + [
			'apiHost'   => 'https://api.sanity.io',
			'projectId' => null,
			'dataset'   => null,
		];

		return new ImageUrlBuilder( null, [
			'baseUrl'   => preg_replace( '/^https:\/\/api\./', 'https://cdn.', $apiHost ),
			'projectId' => $projectId,
			'dataset'   => $dataset,
		] );
	}

	if ( isSanityClientLike( $options ) ) {
		[ 'apiHost' => $apiHost, 'projectId' => $projectId, 'dataset' => $dataset ] = $options[ 'clientConfig' ] + [
			'apiHost'   => 'https://api.sanity.io',
			'projectId' => null,
			'dataset'   => null,
		];

		return new ImageUrlBuilder( null, [
			'baseUrl'   => preg_replace( '/^https:\/\/api\./', 'https://cdn.', $apiHost ),
			'projectId' => $projectId,
			'dataset'   => $dataset,
		] );
	}

	return new ImageUrlBuilder( null, $options );
}

class ImageUrlBuilder {
	public $options;

	public function __construct( ImageUrlBuilder $parent = null, array $options = null ) {
		$this->options = ( $parent )
			? array_merge( $parent->options ?? [], $options ?? [] ) // Merge parent options
			: ( $options ?? [] ); // Copy options
	}

	public function withOptions( array $options ): ImageUrlBuilder {
		$baseUrl = $options[ 'baseUrl' ] ?? $this->options[ 'baseUrl' ] ?? null;

		$newOptions = [ 'baseUrl' => $baseUrl ];
		foreach ( $options as $key => $value ) {
			$specKey                = rewriteSpecName( $key );
			$newOptions[ $specKey ] = $value;
		}

		return new self( $this, $newOptions );
	}

	/**
	 * The image to be represented. Accepts a Sanity 'image'-document, 'asset'-document or
	 * _id of asset. To get the benefit of automatic hot-spot/crop integration with the content
	 * studio, the 'image'-document must be provided.
	 *
	 * @param array|string $source
	 *
	 * @return ImageUrlBuilder
	 */
	public function image( $source ): ImageUrlBuilder {
		return $this->withOptions( [ 'source' => $source ] );
	}

	// Specify the dataset
	public function dataset( string $dataset ): ImageUrlBuilder {
		return $this->withOptions( [ 'dataset' => $dataset ] );
	}

	// Specify the projectId
	public function projectId( string $projectId ): ImageUrlBuilder {
		return $this->withOptions( [ 'projectId' => $projectId ] );
	}

	// Specify background color
	public function bg( string $bg ): ImageUrlBuilder {
		return $this->withOptions( [ 'bg' => $bg ] );
	}

	// Set DPR scaling factor
	public function dpr( float $dpr ): ImageUrlBuilder {
		return $this->withOptions( ( $dpr && $dpr !== 1.0 ) ? [ 'dpr' => $dpr ] : [] );
	}

	// Specify the width of the image in pixels
	public function width( float $width ): ImageUrlBuilder {
		return $this->withOptions( [ 'width' => $width ] );
	}

	// Specify the height of the image in pixels
	public function height( float $height ): ImageUrlBuilder {
		return $this->withOptions( [ 'height' => $height ] );
	}

	// Specify focal point in fraction of image dimensions. Each component 0.0-1.0
	public function focalPoint( float $x, float $y ): ImageUrlBuilder {
		return $this->withOptions( [ 'focalPoint' => [ 'x' => $x, 'y' => $y ] ] );
	}

	public function maxWidth( float $maxWidth ): ImageUrlBuilder {
		return $this->withOptions( [ 'maxWidth' => $maxWidth ] );
	}

	public function minWidth( float $minWidth ): ImageUrlBuilder {
		return $this->withOptions( [ 'minWidth' => $minWidth ] );
	}

	public function maxHeight( float $maxHeight ): ImageUrlBuilder {
		return $this->withOptions( [ 'maxHeight' => $maxHeight ] );
	}

	public function minHeight( float $minHeight ): ImageUrlBuilder {
		return $this->withOptions( [ 'minHeight' => $minHeight ] );
	}

	// Specify width and height in pixels
	public function size( float $width, float $height ): ImageUrlBuilder {
		return $this->withOptions( [ 'width' => $width, 'height' => $height ] );
	}

	// Specify blur between 0 and 100
	public function blur( float $blur ): ImageUrlBuilder {
		return $this->withOptions( [ 'blur' => $blur ] );
	}

	public function sharpen( float $sharpen ): ImageUrlBuilder {
		return $this->withOptions( [ 'sharpen' => $sharpen ] );
	}

	// Specify the desired rectangle of the image
	public function rect( float $left, float $top, float $width, float $height ): ImageUrlBuilder {
		return $this->withOptions( [
			'rect' => [
				'left'   => $left,
				'top'    => $top,
				'width'  => $width,
				'height' => $height
			]
		] );
	}

	// Specify the image format of the image. 'jpg', 'pjpg', 'png', 'webp'
	public function format( string $format ): ImageUrlBuilder {
		return $this->withOptions( [ 'format' => $format ] );
	}

	public function invert( bool $invert ): ImageUrlBuilder {
		return $this->withOptions( [ 'invert' => ( $invert ) ? 'true' : 'false' ] );
	}

	// Rotation in degrees 0, 90, 180, 270
	public function orientation( float $orientation ): ImageUrlBuilder {
		return $this->withOptions( [ 'orientation' => $orientation ] );
	}

	// Compression quality 0-100
	public function quality( float $quality ): ImageUrlBuilder {
		return $this->withOptions( [ 'quality' => $quality ] );
	}

	/**
	 * Make it a download link. Parameter is default filename.
	 *
	 * @param bool|string $download
	 *
	 * @return ImageUrlBuilder
	 */
	public function forceDownload( $download ): ImageUrlBuilder {
		return $this->withOptions( [ 'download' => $download ] );
	}

	// Flip image horizontally
	public function flipHorizontal(): ImageUrlBuilder {
		return $this->withOptions( [ 'flipHorizontal' => true ] );
	}

	// Flip image vertically
	public function flipVertical(): ImageUrlBuilder {
		return $this->withOptions( [ 'flipVertical' => true ] );
	}

	// Ignore crop/hotspot from image record, even when present
	public function ignoreImageParams(): ImageUrlBuilder {
		return $this->withOptions( [ 'ignoreImageParams' => true ] );
	}

	public function fit( string $value ): ImageUrlBuilder {
		if ( ! in_array( $value, VALID_FITS ) ) {
			throw new \InvalidArgumentException( "Invalid fit mode \"$value\"" );
		}

		return $this->withOptions( [ 'fit' => $value ] );
	}

	public function crop( string $value ): ImageUrlBuilder {
		if ( ! in_array( $value, VALID_CROPS ) ) {
			throw new \InvalidArgumentException( "Invalid crop mode \"$value\"" );
		}

		return $this->withOptions( [ 'crop' => $value ] );
	}

	// Saturation
	public function saturation( float $saturation ): ImageUrlBuilder {
		return $this->withOptions( [ 'saturation' => $saturation ] );
	}

	public function auto( string $value ): ImageUrlBuilder {
		if ( ! in_array( $value, VALID_AUTO_MODES ) ) {
			throw new \InvalidArgumentException( "Invalid auto mode \"$value\"" );
		}

		return $this->withOptions( [ 'auto' => $value ] );
	}

	// Specify the number of pixels to pad the image
	public function pad( float $pad ): ImageUrlBuilder {
		return $this->withOptions( [ 'pad' => $pad ] );
	}

	public function frame( float $frame ): ImageUrlBuilder {
		if ( $frame !== 1.0 ) {
			throw new \InvalidArgumentException( "Invalid frame value \"$frame\"" );
		}

		return $this->withOptions( [ 'frame' => $frame ] );
	}

	// Gets the url based on the submitted parameters

	/**
	 * @throws \Exception
	 */
	public function url(): string {
		return urlForImage( $this->options );
	}

	public function toString(): string {
		return $this->url();
	}

	public function __toString(): string {
		return $this->url();
	}
}
