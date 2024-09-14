<?php

namespace SanityImageUrl;

function isSanityModernClientLike( $client ): bool {
	return $client && is_object( $client ) && method_exists( $client, 'config' );
}

function isSanityClientLike( $client ): bool {
	return $client && is_array( $client ) && isset( $client[ 'clientConfig' ] );
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
