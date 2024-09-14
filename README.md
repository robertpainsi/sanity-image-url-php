# PHP port of `@sanity/image-url`

This PHP library is a direct port of the official Sanity JavaScript library [@sanity/image-url](https://github.com/sanity-io/image-url). Version maps to [@sanity/image-url @fff9fc1](https://github.com/sanity-io/image-url/commit/fff9fc1b77f334be195a65394bed71aeffa8f3bb) Aug 8, 2024.

Quickly generate image urls from Sanity image records.

This helper will by default respect any crops/hotspots specified in the Sanity content provided to it. The most typical use case for this is to give it a sanity image and specify a width, height or both and get a nice, cropped and resized image according to the wishes of the content editor and the specifications of the front end developer.

In addition to the core use case, this library provides a handy builder to access the rich selection of processing options available in the Sanity image pipeline.

## Requirements

sanity-image-url-php requires PHP >= 7.4.

## Composer

You can install the library via [Composer](https://getcomposer.org/). Run the following command:

```bash
composer require robertpainsi/sanity-image-url-php
```

To use the library, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

```php
require_once 'vendor/autoload.php';
```

## Usage

The most common way to use this library in your project is to configure it by passing it your sanity client configuration. That way it will automatically be preconfigured to your current project and dataset:


```php
use function SanityImageUrl\urlBuilder;

$builder = urlBuilder( [
    'projectId' => 'zp7mbokg',
    'dataset'   => 'production',
    // ...
] );

function urlFor( $source ) {
    global $builder;

	return $builder->image( $source );
}
```

When working with the official [sanity-php](https://github.com/sanity-io/sanity-php) library, you can also initialize the `urlBuilder` by passing the `Sanity\Client` as parameter.

```php
const SANITY_CONFIG = [
    'projectId' => 'zp7mbokg',
    'dataset'   => 'production',
    // ...
];
$client  = new Sanity\Client( SANITY_CONFIG );
$builder = urlBuilder( $client );
```

Once the builder is initialized, you can use the handy builder syntax to generate your urls:

```
<img src="<?php echo urlFor( $author[ 'image' ] )->width( 200 )->url(); ?>" />
```

This will ensure that the author image is always 200 pixels wide, automatically applying any crop specified by the editor and cropping towards the hot-spot she drew. You can specify both width and height like this:

```
<img src="<?php echo urlFor( $movie[ 'poster' ] )->width( 500 )->height( 300 )->url(); ?>" />
```

There are a large number of useful options you can specify, like e.g. blur:

```
<img src="<?php echo urlFor( $mysteryPerson[ 'mugshot' ] )->width( 200 )->height( 200 )->blur( 50 )->url(); ?>" />
```

Note that the `url()` function needs to be the final one in order to output the url as a string.

## Full usage example

```typescript
// author.ts
export default {
    name: 'author',
    type: 'document',
    title: 'Author',
    fields: [{
        name: 'image',
        type: 'image',
        title: 'Image',
        options: {
            hotspot: true,
        }
    }]
}
```

```php
// author.php
require_once 'vendor/autoload.php';

const SANITY_CONFIG = [
    'projectId' => 'zp7mbokg',
    'dataset'   => 'production',
];

$client  = new Sanity\Client( SANITY_CONFIG );
$builder = urlBuilder( $client );

$authors = $client->fetch( "*[_type == 'author']" );
foreach ( $authors as $author ) {
	echo '<img src="' . $builder->image( $author[ 'image' ] )->width( 320 )->height( 640 )->url() . '" />';
}
```

## Builder methods

### `image( $source )`

Specify the image to be rendered. Accepts either a Sanity `image` record, an `asset` record, or just the asset id as a string. In order for hotspot/crop processing to be applied, the `image` record must be supplied, as well as both width and height.

### `dataset( $dataset )`, `projectId( $projectId )`

Usually you should preconfigure your builder with dataset and project id, but even when you did, these let you temporarily override them if you need to render assets from other projects or datasets.

### `width( $pixels )`

Specify the width of the rendered image in pixels.

### `height( $pixels )`

Specify the height of the rendered image in pixels.

### `size( $width, $height )`

Specify width and height in one go.

### `focalPoint( $x, $y )`

Specify a center point to focus on when cropping the image. Values from 0.0 to 1.0 in fractions of the image dimensions. When specified, overrides any crop or hotspot in the image record.

### `blur( $amount )`, `sharpen( $amount )`, `invert()`

Apply image processing.

### `rect( $left, $top, $width, $height )`

Specify the crop in pixels. Overrides any crop/hotspot in the image record.

### `format( $name )`

Specify the image format of the image. 'jpg', 'pjpg', 'png', 'webp'

### `auto( $mode )`

Specify transformations to automatically apply based on browser capabilities. Supported values:

- `format` - Automatically uses WebP if supported

### `orientation( $angle )`

Rotation in degrees. Acceptable values: 0, 90, 180, 270

### `quality( $value )`

Compression quality, where applicable. 0-100

### `forceDownload( $defaultFileName )`

Make this an url to download the image. Specify the file name that will be suggested to the user.

### `flipHorizontal()`, `flipVertical()`

Flips the image.

### `crop( $mode )`

Specifies how to crop the image. When specified, overrides any crop or hotspot in the image record. See the [documentation](https://www.sanity.io/docs/image-urls#crop-749d37d946b6) for details.

### `fit( $value )`

Configures the fit mode. See the [documentation](https://www.sanity.io/docs/image-urls#fit-45b29dc6f09f) for details.

### `dpr( $value )`

Specifies device pixel ratio scaling factor. From 1 to 3.

### `saturation( $value )`

Adjusts the saturation of the image. Currently the only supported value is `-100` - meaning it grayscales the image.

### `ignoreImageParams()`

Ignore any specifications from the image record (i.e. crop and hotspot).

### `url()`, `__toString()`

Return the url as a string.

### `pad( $value )`

Specify the number of pixels to pad the image.

### `frame( $value )`

Specify the frame of an animated image to transform.  Acceptable values:

- `1` - Returns the first frame of the animated image as a static preview of the image.

### Deprecated: `minWidth( $pixels )`, `maxWidth( $pixels )`, `minHeight( $pixels )`, `maxHeight( $pixels )`

Specifies min/max dimensions when cropping.

**Deprecated**: You usually want to use `width`/`height` with a fit mode of `max` or `min` instead.

## Custom CDN domains

> ℹ️ This feature is available to select Enterprise accounts. Get in touch with your sales executive to learn more.

You can specify a custom `baseUrl` in the builder options in order to override the default (`https://cdn.sanity.io`):

```php
use function SanityImageUrl\urlBuilder;

$builder = urlBuilder( [
	'baseUrl'   => 'https://my.custom.domain',
	'projectId' => 'abc123',
	'dataset'   => 'production',
] );

echo $builder->image( 'image-928ac96d53b0c9049836c86ff25fd3c009039a16-200x200-png' )
	->auto( 'format' )
	->fit( 'max' )
	->width( 720 )
	->toString();

// output: https://my.custom.domain/images/abc123/production/928ac96d53b0c9049836c86ff25fd3c009039a16-200x200.png?w=720&fit=max&auto=format
```

## Feature requests

Please only request new features on the [official GitHub project](https://github.com/sanity-io/image-url/issues). If implemented, this library will also implement the feature at a later point.

## Issues

Before submitting a new issue, please check if it doesn't exist already. If not, verify if this issue also exists in the [official JavaScript project](https://github.com/sanity-io/image-url). If the JavaScript code and PHP code behave differently, feel free to [report an issue](https://github.com/robertpainsi/sanity-image-url-php/issues/new).

## Pull requests

Only pull requests addressing different behavior between the [official JavaScript project](https://github.com/sanity-io/image-url) and this PHP project will be considered. The PHP code has been ported from the JavaScript code in a near line by line manner, which will make any future updates easier to implement. Changes to the structure or readability will most likely end in slower future updates and should be avoided.

In short, let's try to keep the PHP code and JavaScript code in sync :handshake:

## License

MIT © [Robert Painsi](https://robertpainsi.com)