<?php

/**
 * ImageUrlBuilderOptions class in PHP with similar properties to the TypeScript interface.
 */
class ImageUrlBuilderOptions {
	public ?string $baseUrl = null;
	public $source = null; // Can be of various types like string, SanityReference, etc.
	public ?string $bg = null;
	public ?float $dpr = null;
	public ?float $width = null;
	public ?float $height = null;
	public ?array $focalPofloat = null; // ['x' => float, 'y' => float]
	public ?float $maxWidth = null;
	public ?float $maxHeight = null;
	public ?float $minWidth = null;
	public ?float $minHeight = null;
	public ?float $blur = null;
	public ?float $sharpen = null;
	public ?array $rect = null; // ['left' => int, 'top' => int, 'width' => int, 'height' => int]
	public ?string $format = null; // ImageFormat type
	public ?bool $invert = null;
	public ?float $orientation = null; // Orientation type
	public ?float $quality = null;
	public $download = null; // Can be boolean or string
	public ?bool $flipHorizontal = null;
	public ?bool $flipVertical = null;
	public ?bool $ignoreImageParams = null;
	public ?string $fit = null; // FitMode type
	public ?string $crop = null; // CropMode type
	public ?float $saturation = null;
	public ?string $auto = null; // AutoMode type
	public ?float $pad = null;
	public ?float $frame = null;
}

/**
 * ImageUrlBuilderOptionsWithAliases extends ImageUrlBuilderOptions and adds additional alias properties.
 */
class ImageUrlBuilderOptionsWithAliases extends ImageUrlBuilderOptions {
	public ?float $w = null;
	public ?float $h = null;
	public ?float $q = null;
	public ?string $fm = null;
	public $dl = null; // Can be boolean or string
	public ?float $or = null; // Orientation type
	public ?float $sharp = null;
	public ?float $min_h = null;
	public ?float $max_h = null;
	public ?float $min_w = null;
	public ?float $max_w = null;
	public ?float $sat = null;
	public array $additionalParams = []; // Holds any additional parameters
}

/**
 * ImageUrlBuilderOptionsWithAsset extends ImageUrlBuilderOptions and adds an asset property.
 */
class ImageUrlBuilderOptionsWithAsset extends ImageUrlBuilderOptions {
	public array $asset; // ['id' => string, 'width' => int, 'height' => int, 'format' => string]
	public array $additionalParams = []; // Holds any additional parameters
}

/**
 * SanityClientLike interface.
 */
interface SanityClientLike {
	public function getClientConfig(): array; // ['dataset' => string, 'projectId' => string, 'apiHost' => string]
}

/**
 * SanityModernClientLike interface.
 */
interface SanityModernClientLike {
	public function config(): array; // ['dataset' => string, 'projectId' => string, 'apiHost' => string]
}

/**
 * SanityProjectDetails class in PHP.
 */
class SanityProjectDetails {
	public string $projectId;
	public string $dataset;

	public function __construct( string $projectId, string $dataset ) {
		$this->projectId = $projectId;
		$this->dataset   = $dataset;
	}
}

/**
 * SanityReference interface.
 */
class SanityReference {
	public string $_ref;
}

/**
 * SanityImageWithAssetStub class.
 */
class SanityImageWithAssetStub {
	public array $asset; // ['url' => string]
}

/**
 * SanityAsset class.
 */
class SanityAsset {
	public ?string $_id = null;
	public ?string $url = null;
	public ?string $path = null;
	public ?string $assetId = null;
	public ?string $extension = null;
	public array $additionalParams = [];
}

/**
 * SanityImageDimensions class.
 */
class SanityImageDimensions {
	public float $aspectRatio;
	public float $height;
	public float $width;
}

/**
 * SanityImageFitResult class.
 */
class SanityImageFitResult {
	public ?float $width = null;
	public ?float $height = null;
	public array $rect; // ['left' => int, 'top' => int, 'width' => int, 'height' => int]
}

/**
 * SanityImageRect class.
 */
class SanityImageRect {
	public float $left;
	public float $top;
	public float $width;
	public float $height;
}

/**
 * SanityImageCrop class.
 */
class SanityImageCrop {
	public ?string $_type = null;
	public float $left;
	public float $bottom;
	public float $right;
	public float $top;
}

/**
 * SanityImageHotspot class.
 */
class SanityImageHotspot {
	public ?string $_type = null;
	public float $width;
	public float $height;
	public float $x;
	public float $y;
}

/**
 * SanityImageObject class.
 */
class SanityImageObject {
	public $asset; // SanityReference or SanityAsset
	public ?SanityImageCrop $crop = null;
	public ?SanityImageHotspot $hotspot = null;
}

/**
 * CropSpec class.
 */
class CropSpec {
	public float $left;
	public float $top;
	public float $width;
	public float $height;
}

/**
 * HotspotSpec class.
 */
class HotspotSpec {
	public float $left;
	public float $top;
	public float $right;
	public float $bottom;
}
