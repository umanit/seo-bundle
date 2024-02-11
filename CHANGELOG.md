# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- Missing tag on repository

## [3.0.1] - 2024-02-10

### Changed

- Fix deprecated

## [3.0.0] - 2024-02-10

### Added

- Support for Symfony 7

### Fixed

- Automatically support attribute on entities

### Removed

- Drop support for PHP < 8.1
- Drop support for Symfony < 6.4
- Drop support for Twig < 3.0

## [2.2.1] - 2023-05-25

### Added

- Symfony 6 support

## [2.2.0] - 2023-01-13

### Added

- Adds Doctrine attributes to all classes and properties to make them usable whether the ORM mapping is configured to
  read attributes (`doctrine/orm` >= 2.9) or annotations

## [2.1.5] - 2023-01-12

### Fixed

- Fixes the call of Sonata `AbstractAdmin` method from `canAccessObject` to `hasAccess`, in accordance with its new name

## [2.1.4] - 2023-01-12

### Changed

- Updates `SeoAdminExtension` method declarations to be compatible with Sonata Admin 4

## [2.1.3] - 2023-01-12

### Removed

- Removes the `symfony/flex` requirement

## [2.1.2] - 2021-10-14

### Fixed

- Fix a wrong call to decorated method

## [2.1.1] - 2021-08-02

###Added

- Add PHP 8.0 compatiblity.

## [2.1.0] - 2021-06-18

### Changed

- Use a custom Entity Manager to manipulate UrlPooler and URL historization. These modifications should definitely fix
  problems with other entities side effects.

### Removed

- Remove the `flush` method on `UrlPoolerInterface`.
    * ⚠️ Potential BC Break if you have overide the service `@umanit_seo.url_history.url_pooler` ⚠️

## [2.0.8] - 2021-05-25

### Added

- Add compatibility with Symfony 5.3.

## [2.0.7] - 2021-04-26

### Fixed

- Remove the code from [2.0.1] which is a bad solution with more problems than solutions. Instead, each entity are
  flushed one by one which is not a good solution either because it will not work anymore with Doctrine 3. However, it's
  a good temporary solution for now.

## [2.0.6] - 2021-04-16

### Fixed

- The argument `$entity` for `UrlPoolerInterface::processEntityDependency` is a non sens and was removed
    * ⚠️ Potential BC Break if you have overide the service `@umanit_seo.url_history.url_pooler` ⚠️

## [2.0.5] - 2021-03-05

### Added

- Handle parameters which are not in the `entity` route when using `path`, `url` and `seo_canonical` Twig functions

## [2.0.4] - 2021-02-21

### Fixed

- Fix crash when `seo_schema_org` when both given entity and current entity are null
- Fix crash when `$reflectionEntity` is `null` in `UrlHistoryWriter::loadClassMetadata`

## [2.0.3] - 2021-02-06

### Fixed

- Merge attr option with existing when calculating default SEO values

### Added

- Add an interface `EntityParserInterface` on `Title` and `Excerpt` entities parsers

### Changed

- Use the entity `EntityParserInterface` in `SeoMetadataResolver`

## [2.0.2] - 2021-02-06

### Fixed

- Fix composer version for symfony/twig-bundle

## [2.0.1] - 2021-01-08

### Fixed

- Clear the entity manager before creating redirection in the URL Pool to avoid side effects with other entities.

## [2.0.0] - 2020-12-15

Initial release for the v2.

## [1.2.6] - 2020-05-28

Last release of the v1.

[Unreleased]: https://github.com/umanit/block-collection-bundle/compare/3.0.1...HEAD

[3.0.1]: https://github.com/umanit/block-collection-bundle/compare/3.0.0...3.0.1

[3.0.0]: https://github.com/umanit/block-collection-bundle/compare/2.2.1...3.0.0

[2.2.1]: https://github.com/umanit/block-collection-bundle/compare/2.2.0...2.2.1

[2.2.0]: https://github.com/umanit/block-collection-bundle/compare/2.1.5...2.2.0

[2.1.5]: https://github.com/umanit/block-collection-bundle/compare/2.1.4...2.1.5

[2.1.4]: https://github.com/umanit/block-collection-bundle/compare/2.1.3...2.1.4

[2.1.3]: https://github.com/umanit/block-collection-bundle/compare/2.1.2...2.1.3

[2.1.2]: https://github.com/umanit/block-collection-bundle/compare/2.1.1...2.1.2

[2.1.1]: https://github.com/umanit/block-collection-bundle/compare/2.1.0...2.1.1

[2.1.0]: https://github.com/umanit/block-collection-bundle/compare/2.0.8...2.1.0

[2.0.8]: https://github.com/umanit/block-collection-bundle/compare/2.0.7...2.0.8

[2.0.7]: https://github.com/umanit/block-collection-bundle/compare/2.0.6...2.0.7

[2.0.6]: https://github.com/umanit/block-collection-bundle/compare/2.0.5...2.0.6

[2.0.5]: https://github.com/umanit/block-collection-bundle/compare/2.0.4...2.0.5

[2.0.4]: https://github.com/umanit/block-collection-bundle/compare/2.0.3...2.0.4

[2.0.3]: https://github.com/umanit/block-collection-bundle/compare/2.0.2...2.0.3

[2.0.2]: https://github.com/umanit/block-collection-bundle/compare/2.0.1...2.0.2

[2.0.1]: https://github.com/umanit/block-collection-bundle/compare/2.0.0...2.0.1

[2.0.0]: https://github.com/umanit/block-collection-bundle/releases/tag/2.0.0

[1.2.6]: https://github.com/umanit/block-collection-bundle/releases/tag/1.2.6
