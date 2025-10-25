# dependencies (TYPO3 Extension)

## Features

- Displays a nested Composer dependency tree in the backend
- Shows required, installed, and latest available versions for each package
- Displays categories (functional, e.g. "template-engine") and tags (technical, e.g. "PHP", "Twig")
- Supports per-package override rules via `dependency-rules/`
- Depth and filtering controls in the backend module
- **Caches version lookups** using TYPO3's Caching Framework for performance

## npm Package Support

- The backend module can analyze both Composer (PHP) and npm (JavaScript) dependencies.
- Switch between Composer and npm views using the selector in the backend module.
- Add category/tag mapping for npm packages in `default-dependency-categories.json` (use `npm_categories`, `npm_tags`).
- Add or override npm rules in `dependency-rules/` (same format, one file per package).

## Installation

1. Place this extension in `typo3conf/ext/dependencies`
2. Optionally add `default-dependency-categories.json` and rules in `dependency-rules/`
3. Install and activate the extension in the TYPO3 Extension Manager
4. Access the backend module under "Tools > Dependencies"

## Performance & Caching

- Latest version lookups from Packagist are cached for 1 day.
- Cache is stored in TYPO3's cache system (default: file backend).
- To flush the cache, use the "Flush caches" button in the TYPO3 backend or command line.

## Customization

- Add or edit mapping in `default-dependency-categories.json`
- Override individual packages with JSON files in `dependency-rules/`
- Adjust the Fluid template for custom rendering

## Compatibility

- TYPO3 v12, v13, v14+ (not tested for v11)

## Author

David Bruchmann

