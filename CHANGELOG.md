# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com).

## [1.3.1] - 2017-04-23

### Fixed

- Command `check:links` now skips links starting with `javascript:` or `mailto:`.
- Code inspection issues

## [1.3.0] - 2017-01-07

### Added

- Command `check:links` that checks all HTML links in generated content and all XML sitemap links.

## [1.2.0] - 2016-12-31

### Added

- Command `generate:sitemap` that generates an XML sitemap in `<outputDir>/sitemap.xml`

## [1.1.0] - 2016-10-26

### Added

- Input option `--baseUrl=<baseUrl>` to command `generate:pages` for local overwrite of the base URL in `Project.json`

### Changed

- Default name of config file from `Pages.json` to `Project.json`
 
## 1.0.0 - 2016-10-19

- First stable release

[1.3.1]: https://github.com/icehawk/static-page-generator/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/icehawk/static-page-generator/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/icehawk/static-page-generator/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/icehawk/static-page-generator/compare/v1.0.0...v1.1.0
