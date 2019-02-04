# Umanit Seo Bundle

This bundle adds SEO capabilities for Doctrine entities.

## Features

- [x] 301 Redirects when accessing an old URL
- [x] SEO Metadata (title and description)
- [x] Canonical URL
- [x] Schema.org
- [ ] Sitemap

## Installation

`$ composer require umanit/seo-bundle`


## Usage

1. [Basic Usage](#basic-usage)
1. [Seo Metadata](#seo-metadata)
1. [Schema.org](#schema.org-implementation)
1. [Breadcrumb](#breadcrumb)
1. [Enabling 301 redirects](#enabling-301-redirects)
1. [Twig functions reference](#twig-functions-reference)
1. [Full usage example](#full-usage-example)

### Basic usage

In order to function properly, SeoBundle must be able to generate a URL for a given entity.

**Add the `@Seo\Route` annotation to your entity**

The `@Seo\Route` annotation needs to know how to generate a url from the entity.
The first argument is the route name associated to your entity, the second is the parameters needed to generate the route.

`routeParameters` takes as many `@RouteParameter` as needed.

`@RouteParameter` takes two arguments:
* `parameter`: The name of the route parameter
* `property`: The property associated

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Doctrine\Annotation as Seo;

/**
 * Class Page
 *
 * @Seo\Route(
 *     routeName="app_page_show",
 *     routeParameters={
 *         @RouteParameter(parameter="slug", property="slug")
 * })
 * @ORM\Entity()
 */
class page
{
    // ...
}
```

SeoBundle will now be able to generate a URL from the entity.
If you ever change the slug of a page, the old URL will be redirected to the new one.

If you wanted to generate the URL by yourself you would have done something like the following example:

```html
{{ path('app_page_show', { 'slug': my_page.slug }) }}"
```

You can now do like so:

```twig
{{ path(my_page) }}
```

___Note:__ You can use the `canonical()` function without passing it an entity, SeoBundle will automatically resolve the entity associated to the current accessed route and generate the url from it._

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

namespace App\Entity;

use Umanit\SeoBundle\Doctrine\Model\HasSeoMetadataInterface;
use Umanit\SeoBundle\Doctrine\Model\SeoMetadataTrait;

class Page implements HasSeoMetadataInterface
{
    use SeoMetadataTrait;
    
    // ...
}
```

Next in your admin form, use the `SeoMetadataType` form type.

```php
use Umanit\SeoBundle\Form\Type\SeoMetadataType;

$builder->add('seoMetadata', SeoMetadataType::class);
```

This will add a subform with two fields, `title` and `description`.

### Schema.org implementation

To generate valid [schema.org](https://schema.org/) json microdata, add the `@Seo\SchemaOrgBuilder` annotation to your entity.

This annotation takes either a service id or a method of the entity.

Use the library [spatie/schema-org](https://github.com/spatie/schema-org) to generate your schema.

__Example:__

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Doctrine\Annotation as Seo;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;

/**
 * @ORM\Entity()
 * @Seo\SchemaOrgBuilder("buildSchemaOrg")
 */
class Page
{
    // ...
    
    /**
     * Builds the schema.org.
     *
     * @return BaseType
     */
    public function buildSchemaOrg() : BaseType
    {
        // Build the schema.org to you needs.
        return
            Schema::mensClothingStore()
                  ->name($this->getName())
                  ->email($this->getAuthor()->getEmail())
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
</script>\n
```

### Breadcrumb

You can easily generate your breadcrumb in 3 different formats; `Microdata`, `RDFa` or `JSON-LD` as described by [the specification](https://schema.org/BreadcrumbList).

Use the `@Seo\Breadcrumb` annotation on your entity. It takes two arguments, the first one is a collection of `@Seo\BreadcrumbItem`, the second one is the format you want, default is `'microdata'`.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Doctrine\Annotation as Seo;

/**
 * @ORM\Entity()
 * @Seo\Route(
 *     routeName="app_page_show",
 *     routeParameters={
 *         @RouteParameter(parameter="slug", property="slug"),
 *         @RouteParameter(parameter="category", property="category.slug")
 * })
 * @Seo\Breadcrumb({
 *     @Seo\BreadcrumbItem("app_home_page", name="Home"),
 *     @Seo\BreadcrumbItem("category", name="category.slug"),
 *     @Seo\BreadcrumbItem(name="name"),
 * })
 */
class Page
{
    
}
```

`@Seo\BreadcrumbItem` takes two optional arguments:
1. `value` (the first arg) is either a route name, or the path to a child entity.
 /!\ The child entity must also be annotated with `Seo\Route`. It is used to generate the url of the breadcrumb item.
  **Note:** leave it blank to generate a url from `$this` (`Page` in this example).
1. `name` is either a path to a property of the current entity or a simple string.

You can now use the twig function `seo_breadcrumb()` like the following examples:

```twig
{{ seo_breadcrumb() }} {# Will generate the breadcrumb from the current entity using microdata format #}
{{ seo_breadcrumb(entity=my_entity, format='json-ld') }} {# Will generate the breadcrumb from my_entity using json-ld format #}
{{ seo_breadcrumb(format='rdfa') }} {# Will generate the breadcrumb from the current entity using rdfa format #}
```

### Enabling 301 redirects

In order to enable URL history and 301 redirects on an Entity, implement the interface `UrlHistorizedInterface` and use the trait `UrlHistorizedTrait`.

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

### Full usage example

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Doctrine\Model\HasSeoMetadataInterface;
use Umanit\SeoBundle\Doctrine\Model\SeoMetadataTrait;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedInterface;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedTrait;
use Umanit\SeoBundle\Doctrine\Annotation as Seo;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;

/**
 * Class Page
 *
 * @ORM\Entity()
 * @Seo\Route(
 *     routeName="app_page_show",
 *     routeParameters={
 *         @Seo\RouteParameter(parameter="slug", property="slug"),
 *         @Seo\RouteParameter(parameter="category", property="category.slug")
 * })
 * @Seo\SchemaOrgBuilder("buildSchemaOrg")
 * @Seo\Breadcrumb({
 *     @Seo\BreadcrumbItem("app_home_page", name="Home"),
 *     @Seo\BreadcrumbItem("category", name="category.slug"),
 *     @Seo\BreadcrumbItem(name="name"),
 * })
 */
class Page implements HasSeoMetadataInterface, UrlHistorizedInterface
{
    use SeoMetadataTrait, UrlHistorizedTrait;

    /**
     * The identifier of Page.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The name of the Page
     *
     * @var string
     * @ORM\Column(nullable=true)
     */
    private $name;

    /**
     * The introduction of the Page
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $introduction;

    /**
     * The slug of the Page
     *
     * @var string
     * @ORM\Column(unique=true)
     */
    private $slug;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", cascade={"all"})
     */
    private $category;
    
    // Getters and setters...
    
    /**
     * Builds the schema.org.
     * 
     * @return BaseType
     */
    public function buildSchemaOrg() : BaseType
    {
        return Schema::webPage()
                       ->name($this->name);
    }
}
```
