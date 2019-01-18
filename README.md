# Umanit Seo Bundle

This bundle adds SEO capabilities for Doctrine entities.

## Features

- [x] 301 Redirects when accessing an old URL.
- [ ] SEO Metatags (title, description, )
- [ ] Canonical URL
- [ ] Sitemap
- [ ] Schema.org

## Installation

`$ composer require umanit/seo-bundle`


## Usage

1. [Preparing your entities](#preparing-your-entities)

### Preparing your entities

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

```twig
{{ path('app_page_show', { 'slug': my_page.slug } ) }}
```

You can now do like so:

```twig
{{ canonical(my_page) }}
```
