# IceHawk\StaticPageGenerator

## Available commands

Please run any command with `-h` to get detailed description.

### Generate pages
 
`generate:pages [-b <baseUrl>|--baseUrl=<baseUrl>] /path/to/Project.json`

### Generate XML sitemap

`generate:sitemap [-b <baseUrl>|--baseUrl=<baseUrl>] /path/to/Project.json`

### Check links

`check:links [-g|--generate] [-t|--timeout=<sec>] [-b <baseUrl>|--baseUrl=<baseUrl>] /path/to/Project.json`

**Note:**
 * All external links (not on configured base URL) are skipped
 * relative URLs will be converted to full URLs with configured base URL and will be checked
 * Anchor links will be converted to full URLs with configured base URL and will be checked
 * Use switch `-v` to see all skipped links in output
