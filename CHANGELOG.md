# CHANGELOG

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com).

## [1.4.1] - 2018-01-07

### Fixed

- Command `check:links` does not require a valid SSL certificate on `https://` links anymore.

## [1.4.0] - 2018-01-03

### Added

- Command `generate:search-index` that generates a static JSON file for a JavaScript on-page search - [#5]

## [1.3.1] - 2017-04-23

### Fixed

- Command `check:links` now skips links starting with `javascript:` or `mailto:`.  [#9]
- Code inspection issues

## [1.3.0] - 2017-01-07

### Added

- Command `check:links` that checks all HTML links in generated content and all XML sitemap links. - [#4]

## [1.2.0] - 2016-12-31

### Added

- Command `generate:sitemap` that generates an XML sitemap in `<outputDir>/sitemap.xml` - [#6]

## [1.1.0] - 2016-10-26

### Added

- Input option `--baseUrl=<baseUrl>` to command `generate:pages` for local overwrite of the base URL in `Project.json` - [#2]

### Changed

- Default name of config file from `Pages.json` to `Project.json`
 
## 1.0.0 - 2016-10-19

- First stable release

[1.4.1]: https://github.com/icehawk/static-page-generator/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/icehawk/static-page-generator/compare/v1.3.1...v1.4.0
[1.3.1]: https://github.com/icehawk/static-page-generator/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/icehawk/static-page-generator/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/icehawk/static-page-generator/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/icehawk/static-page-generator/compare/v1.0.0...v1.1.0

[#2]: https://github.com/icehawk/static-page-generator/issues/2
[#4]: https://github.com/icehawk/static-page-generator/issues/4
[#5]: https://github.com/icehawk/static-page-generator/issues/5
[#6]: https://github.com/icehawk/static-page-generator/issues/6
[#9]: https://github.com/icehawk/static-page-generator/issues/9
