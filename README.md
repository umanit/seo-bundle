# Umanit Seo Bundle

This bundle adds SEO capabilities for any model entities.

## Features

- [x] 301 Redirects when accessing an old URL
- [x] SEO Metadata (title and description)
- [x] Canonical URL
- [x] Schema.org
- [x] Breadcrumb
- [ ] Sitemap

## Installation

`$ composer require umanit/seo-bundle`

## Configuration

The template needs to be declared in your Twig configuration, **before** other templates:
```yaml
twig:
    # ...
    form_themes:
        - '@UmanitSeo/form/fields.html.twig'
        - ...
```

You can configure your bundle further by creating a `umanit_seo.yaml` configuration file. Here is the default
configuration provided by the bundle:
```yaml
# Default configuration for extension with alias: "umanit_seo"
umanit_seo:

    # Historize URLs of entities which implements HistorizableUrlModelInterface
    url_historization:
        enabled:              true

        # Redirect code used by UrlRedirectorSubscriber
        redirect_code:        301

        # Cache service used to store entities dependencies. **MUST** implements \Symfony\Contracts\Cache\CacheInterface
        cache_service:        cache.app

    # Defines the default templates used to render breadcrumbs
    templates:
        breadcrumb_json_ld:   '@UmanitSeo/breadcrumb/breadcrumb.json-ld.html.twig'
        breadcrumb_microdata: '@UmanitSeo/breadcrumb/breadcrumb.microdata.html.twig'
        breadcrumb_rdfa:      '@UmanitSeo/breadcrumb/breadcrumb.rdfa.html.twig'
    metadata:
        form_type:

            # Automaticaly add a SeoMetadataType on FormType which handled an entity which implements HasSeoMetadataInterface
            add_seo_metadata_type: true

            # FQCN of the FormType used to renders SEO Metadata fields
            class_fqcn:           Umanit\SeoBundle\Form\Type\SeoMetadataType

            # Injects Google Code Prettify when rendering breadcrumb and schema.org in FormType.
            inject_code_prettify: true
        default_title:        'Umanit Seo - Customize this default title to your needs.'
        title_prefix:         ''
        title_suffix:         ''
        default_description:  'Umanit Seo - Customize this default description to your needs.'
```

## Usage

1. [Basic Usage](#basic-usage)
1. [Seo Metadata](#seo-metadata)
1. [Schema.org](#schemaorg-implementation)
1. [Breadcrumb](#breadcrumb)
1. [Enabling 301 redirects](#enabling-301-redirects)
1. [Twig functions reference](#twig-functions-reference)
1. [Protips](#protips)

### Basic usage

In order to function properly, SeoBundle must be able to generate a URL for a given entity. To do so, the
`umanit_seo.routable` service uses handlers to process the entity.

A handler is a service which implements `Umanit\SeoBundle\Handler\Routable\RoutableHandlerInterface`. A `supports`
method indicated if the service can handle the given entity and a `process` method do the job by returning a
`Umanit\SeoBundle\Model\Route` object.

The `Umanit\SeoBundle\Model\Route` object has a `name` attribute, which is the name of the route used to access the
entity and a `parameters` attribute used to build the route.

**You must implement the interface `Umanit\SeoBundle\Model\RoutableModelInterface` on your entity and create a handler
to process it.**

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Model\RoutableModelInterface;

/**
 * @ORM\Entity()
 */
class Page implements RoutableModelInterface
{
    // ...
}
```

```php
<?php

declare(strict_types=1);

namespace App\Seo\Routable;

use App\Entity\Page;
use Umanit\SeoBundle\Handler\Routable\RoutableHandlerInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\Route;

class PageHandler implements RoutableHandlerInterface
{
    public function supports(RoutableModelInterface $entity): bool
    {
        return $entity instanceof Page;
    }

    public function process(RoutableModelInterface $entity): Route
    {
        return new Route('app_page_show', ['slug' => $entity->getSlug()]);
    }
}
```

SeoBundle will now be able to generate a URL from the entity.
If you ever change the slug of a page, the old URL will be redirected to the new one.

If you wanted to generate the URL by yourself you would have done something like the following example:

```twig
{{ path('app_page_show', { 'slug': my_page.slug }) }}"
```

You can now do like so:

```twig
{{ path(my_page) }}
```

_**Note:** You can use the `canonical()` function without passing it an entity, SeoBundle will automatically resolve
the entity associated to the current accessed route and generate the url from it._

Usually, you'll want to use the `canonical()` function directly within your main layout.

### Seo Metadata

Use the `seo_metadata(your_entity)` twig function in your templates.

SeoBundle will automatically find the most pertinent fields in your entity to deduct title and description.

Again, `seo_metadata()` can be used without passing it any entity.

#### Administrating metadata

In order to administrate Seo Metadata, you'll need again to tune-up your entity.

Make your entity implement the `HasSeoMetadataInterface` and use the `SeoMetadataTrait`

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Umanit\SeoBundle\Doctrine\Model\SeoMetadataTrait;
use Umanit\SeoBundle\Model\HasSeoMetadataInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;

class Page implements RoutableModelInterface, HasSeoMetadataInterface
{
    use SeoMetadataTrait;

    // ...
}
```

If the configuration `umanit_seo.metadata.form_type.add_seo_metadata_type` is not disabled, all form which handles your
entity as `data` will automatically have a new `SeoMetadataType` form type.

This will add a subform with two fields, `title` and `description`.

_**Note:** The form type class can be customized with `umanit_seo.metadata.form_type.class_fqcn`._

### Schema.org implementation

To generate valid [schema.org](https://schema.org/) json microdata, SeoBundle must be able to process the given entity.
To do so, the `umanit_seo.schemable` service uses handlers to process the entity.

A handler is a service which implements `Umanit\SeoBundle\Handler\Schemable\SchemableHandlerInterface`. A `supports`
method indicated if the service can handle the given entity and a `process` method do the job by returning a
`Spatie\SchemaOrg\BaseType` object.

The `Spatie\SchemaOrg\BaseType` object is provided by the library
[spatie/schema-org](https://github.com/spatie/schema-org).

**You must implement the interface `Umanit\SeoBundle\Model\SchemableModelInterface` on your entity and create a handler
to process it.**

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Model\SchemableModelInterface;

/**
 * @ORM\Entity()
 */
class Page implements SchemableModelInterface
{
    // ...
}
```

```php
<?php

declare(strict_types=1);

namespace App\Seo\Schemable;

use App\Entity\Page;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Umanit\SeoBundle\Handler\Schemable\SchemableHandlerInterface;
use Umanit\SeoBundle\Model\SchemableModelInterface;

class PageHandler implements SchemableHandlerInterface
{
    public function supports(SchemableModelInterface $entity): bool
    {
        return $entity instanceof Page;
    }

    public function process(SchemableModelInterface $entity): BaseType
    {
        /** @var $entity Page */

        return Schema::mensClothingStore()
                     ->name($entity->getName())
                     ->url($entity->getSlug())
                     ->contactPoint(Schema::contactPoint()->areaServed('Worldwide'))
            ;
    }
}
```

Next, add the twig function `seo_schema_org()` at the bottom of your layout.

The function will format and display the json schema of the current entity as you defined it.

```html
<script type="application/ld+json">
{
    "@context": "https:\/\/schema.org",
    "@type": "MensClothingStore",
    "name": "Test",
    "email": "test@umanit.fr",
    "contactPoint": {
        "@type": "ContactPoint",
        "areaServed": "Worldwide"
    }
}
</script>
```

### Breadcrumb

You can easily generate your breadcrumb in 3 different formats; `Microdata`, `RDFa` or `JSON-LD` as described by
[the specification](https://schema.org/BreadcrumbList). To do so, the `umanit_seo.breadcrumbable` service uses handlers
to process the entity.

A handler is a service which implements `Umanit\SeoBundle\Handler\Breadcrumbable\BreadcrumbableHandlerInterface`. A
`supports` method indicated if the service can handle the given entity and a `process` method do the job by returning a
`Umanit\SeoBundle\Model\Breadcrumb` object.

The `Umanit\SeoBundle\Model\Breadcrumb` obect has a `format` attribute, which is one of the previously mentionned and a
`items` attributes which is an array of `Umanit\SeoBundle\Model\BreadcrumbItem`. Each `BreadcrumbItem` has a `label`
attribute and an optionnal `url` attribute.

**You must implement the interface `Umanit\SeoBundle\Model\BreadcrumbableModelInterface` on your entity and create a
handler to process it.**

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;

/**
 * @ORM\Entity()
 */
class Page implements BreadcrumbableModelInterface
{
    // ...
}
```

```php
<?php

declare(strict_types=1);

namespace App\Seo\Breadcrumbable;

use App\Entity\Page;
use Umanit\SeoBundle\Handler\Breadcrumbable\BreadcrumbableHandlerInterface;
use Umanit\SeoBundle\Model\Breadcrumb;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\BreadcrumbItem;

class PageHandler implements BreadcrumbableHandlerInterface
{
    public function supports(BreadcrumbableModelInterface $entity): bool
    {
        return $entity instanceof Page;
    }

    public function process(BreadcrumbableModelInterface $entity): Breadcrumb
    {
        /** @var $entity Page */
        $breadcrumb = new Breadcrumb();

        $breadcrumb->setItems([
            new BreadcrumbItem('Homepage', '/'),
            new BreadcrumbItem(
                $entity->getCategory()->getName(),
                $this->router->generate('app_page_category_show', ['slug' => $entity->getCategory()->getSlug()])
            ),
            new BreadcrumbItem($entity->getName()),
        ]);

        return $breadcrumb;
    }
}
```

_**Note:** If the processed entity implements the `RoutableModelInterface`, you can omit the `url` attribute to let
the service `umanit_seo.routable` generate it for you._

You can now use the twig function `seo_breadcrumb()` like the following examples:

```twig
{{ seo_breadcrumb() }} {# Will generate the breadcrumb from the current entity using microdata format #}
{{ seo_breadcrumb(entity=my_entity, format='json-ld') }} {# Will generate the breadcrumb from my_entity using json-ld format #}
{{ seo_breadcrumb(format='rdfa') }} {# Will generate the breadcrumb from the current entity using rdfa format #}
```

### Enabling 301 redirects

In order to enable URL history and 301 redirects on an entity, ensure the configuration
`umanit_seo.url_historization.enabled` is active then implement the interface
`Umanit\SeoBundle\Model\HistorizableUrlModelInterface` and use the trait
`Umanit\SeoBundle\Doctrine\Model\HistorizableUrlTrait`.

### Twig functions reference

```html
{{ path(entity) }}                                 # Path to an Seo entity
{{ url(entity) }}                                  # Url to an Seo entity
{{ seo_canonical(entity = null, overrides = []) }} # Canonical link of an Seo entity
{{ seo_title(entity = null) }}                     # Title (without markup) of an Seo entity
{{ seo_metadata(entity = null) }}                  # Metadata of an entity (title and description, with markup)
{{ seo_schema_org(entity = null) }}                # Json schema of an entity (with markup)
{{ seo_breadcrumb(entity = null, format = null) }} # Breadcrumb from an entity (default format to 'microdata')
```

### Protips

 * The `HistorizableUrlModelInterface` extends the `RoutableModelInterface`, so you don't need to implement both,
 * you can use a custom HTTP code when redirecting by overriding `umanit_seo.url_historization.redirect_code`,
 * you can use a custom cache service for `Umanit\SeoBundle\Doctrine\EventSubscriber\UrlHistoryWriter` by overriding
 `umanit_seo.url_historization.cache_service`,
 * if one of your service needs the `@router`, you can implement `Umanit\SeoBundle\Service\RouterAwareInterface` and
 use the trait `Umanit\SeoBundle\Service\RouterAwareTrait` (usefull for breadcrumb handlers!).
