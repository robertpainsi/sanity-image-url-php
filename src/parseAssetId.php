<?php

/**
 * @link https://github.com/sanity-io/image-url/blob/main/src/parseAssetId.ts
 */

namespace SanityImageUrl;

function parseAssetId(string $ref): array
{
    $example = 'image-Tb9Ew8CXIwaY6R1kjMvI0uRR-2000x3000-jpg';

    $parts = explode('-', $ref);
    if (count($parts) < 4) {
        throw new \Exception("Malformed asset _ref '{$ref}'. Expected an id like \"{$example}\".");
    }

    [, $id, $dimensionString, $format] = $parts;
    if (!$id || !$dimensionString || !$format) {
        throw new \Exception("Malformed asset _ref '{$ref}'. Expected an id like \"{$example}\".");
    }

    $dimensions = explode('x', $dimensionString);
    if (count($dimensions) !== 2) {
        throw new \Exception("Malformed asset _ref '{$ref}'. Expected an id like \"{$example}\".");
    }

    [$imgWidthStr, $imgHeightStr] = $dimensions;

    $width = is_numeric($imgWidthStr) ? (float)$imgWidthStr : NAN;
    $height = is_numeric($imgHeightStr) ? (float)$imgHeightStr : NAN;

    $isValidAssetId = is_finite($width) && is_finite($height);
    if (!$isValidAssetId) {
        throw new \Exception("Malformed asset _ref '{$ref}'. Expected an id like \"{$example}\".");
    }

    return [
        'id' => $id,
        'width' => $width,
        'height' => $height,
        'format' => $format
    ];
}
