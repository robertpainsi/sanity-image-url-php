<?php

/**
 * @link https://github.com/sanity-io/image-url/blob/main/src/parseSource.ts
 */

namespace SanityImageUrl;

function isRef($src): bool
{
    return is_array($src) && isset($src['_ref']) && is_string($src['_ref']);
}

function isAsset($src): bool
{
    return is_array($src) && isset($src['_id']) && is_string($src['_id']);
}

function isAssetStub($src): bool
{
    return is_array($src) && isset($src['asset']) && is_array($src['asset'])
        && isset($src['asset']['url']) && is_string($src['asset']['url']);
}


/**
 * Convert an asset-id, asset or image to an image record suitable for processing
 * eslint-disable-next-line complexity
 *
 * @param string|array|null $source
 *
 * @return array|null
 */
function parseSource($source = null)
{
    if (!$source) {
        return null;
    }

    if (is_string($source) && isUrl($source)) {
        // Someone passed an existing image url?
        $image = [
            'asset' => ['_ref' => urlToId($source)],
        ];
    } elseif (is_string($source)) {
        // Just an asset id
        $image = [
            'asset' => ['_ref' => $source],
        ];
    } elseif (isRef($source)) {
        // We just got passed an asset directly
        $image = [
            'asset' => $source,
        ];
    } elseif (isAsset($source)) {
        // If we were passed an image asset document
        $image = [
            'asset' => [
                '_ref' => $source['_id'] ?? '',
            ],
        ];
    } elseif (isAssetStub($source)) {
        // If we were passed a partial asset (`url`, but no `_id`)
        $image = [
            'asset' => [
                '_ref' => urlToId($source['asset']['url']),
            ],
        ];
    } elseif (is_array($source) && isset($source['asset']) && is_array($source['asset'])) {
        // Probably an actual image with materialized asset
        $image = $source;
    } else {
        // We got something that does not look like an image, or it is an image
        // that currently isn't sporting an asset.
        return null;
    }

    if (isset($source['crop'])) {
        $image['crop'] = $source['crop'];
    }

    if (isset($source['hotspot'])) {
        $image['hotspot'] = $source['hotspot'];
    }

    return applyDefaults($image);
}

function isUrl($url)
{
    return preg_match('/^https?:\/\//', $url) === 1;
}

function urlToId($url)
{
    $parts = explode('/', $url);
    $lastPart = end($parts);

    return preg_replace('/\.([a-z]+)$/', '-$1', "image-$lastPart");
}

function applyDefaults(array $image)
{
    if (isset($image['crop']) && isset($image['hotspot'])) {
        return $image;
    }

    $result = $image;

    if (!isset($result['crop'])) {
        $result['crop'] = [
            'left' => 0.0,
            'top' => 0.0,
            'bottom' => 0.0,
            'right' => 0.0,
        ];
    }

    if (!isset($result['hotspot'])) {
        $result['hotspot'] = [
            'x' => 0.5,
            'y' => 0.5,
            'height' => 1.0,
            'width' => 1.0,
        ];
    }

    return $result;
}
