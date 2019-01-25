# Umanit Seo Bundle

This bundle adds SEO capabilities for Doctrine entities.

## Features

- [x] 301 Redirects when accessing an old URL
- [x] SEO Metadata (title and description)
- [x] Canonical URL
- [ ] Sitemap
- [ ] Schema.org

## Installation

`$ composer require umanit/seo-bundle`


## Usage

1. [Canonical and 301 redirects](#canonical-and-301-redirects)
1. [Seo Metadata](#seo-metadate)

### Canonical and 30 redirects

In order to function properly, SeoBundle must be able to generate a URL for a given entity.

**Add the `@Seo` annotation to your entity**

The `@Seo` annotation needs to know how to generate a url from the entity.
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
use Umanit\SeoBundle\Doctrine\Annotation\Seo;

/**
 * Class SeoPage
 *
 * @Seo(
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
<link rel="canonical" href="{{ path('app_page_show', { 'slug': my_page.slug }) }}">
```

You can now do like so:

```twig
{{ canonical(my_page) }}
```

___Note:__ You can use the `canonical()` function without passing it an entity, SeoBundle will automatically resolve the entity associated to the current accessed route and generate the url from it._

Usually, you'll want to use the `canonical()` function directly within your main layout.

### Seo Metadata

#### Set-up

In order to use Seo Metadata, you'll need again to tune-up your entity.

Make your entity implement the `HasSeoMetadataInterface` and use the `SeoMetadataTrait`

```php
<?php

namespace App\Entity;

use Umanit\Bundle\TreeBundle\Model\HasSeoMetadataInterface;
use Umanit\Bundle\TreeBundle\Model\SeoMetadataTrait;

class page implements HasSeoMetadataInterface
{
    use SeoMetadataTrait;
    
    // ...
}
```

You can now use the `seo_metadata(your_entity)` twig function in your templates.

SeoBundle will automatically find the most pertinent fields in your entity to deduct title and description if those are null.

Again, `seo_metadata()` can be used without passing it any entity.

#### Administrate metadata

To administrate `title` and `description` metadata, use the `SeoMetadataType` form type.

```php
use Umanit\SeoBundle\Form\Type\SeoMetadataType;

$builder->add('seoMetadata', SeoMetadataType::class);
```
