# IceHawk\StaticPageGenerator

## Installation

Download the PHAR file from [latest release](https://github.com/icehawk/static-page-generator/releases/latest).

## Available commands

Please run any command with `-h` to get detailed description.

### Generate pages
 
`generate:pages [-b <baseUrl>|--baseUrl=<baseUrl>] /path/to/Project.json`

### Generate XML sitemap

`generate:sitemap [-b <baseUrl>|--baseUrl=<baseUrl>] /path/to/Project.json`

### Generate search index JSON file

`generate:search-index [-b <baseUrl>|--baseUrl=<baseUrl>] /path/to/Project.json`

### Check links

`check:links [-g|--generate] [-t|--timeout=<sec>] [-b <baseUrl>|--baseUrl=<baseUrl>] /path/to/Project.json`

**Note:**
 * If the `[-g|--generate]` option is set, all commands from the `generate:` namespace will be executed prior to the link checks.
 * All external links (not on configured base URL) are skipped.
 * All links starting with `javascript:` or `mailto:` are skipped.
 * relative URLs will be converted to full URLs with configured base URL and will be checked.
 * Anchor links will be converted to full URLs with configured base URL and will be checked.
 * Use switch `-v` to see all skipped links in output.
