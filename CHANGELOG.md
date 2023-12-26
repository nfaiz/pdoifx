# Changelog

## [v0.9.4](https://github.com/nfaiz/ci4-ifx/compare/v0.9.3...v0.9.4) - 2023-12-26

### Changed
- Breaking Change. Upgrade dbtoolbar to v1.0.0 to make it usable.


## [v0.9.3](https://github.com/nfaiz/ci4-ifx/compare/v0.9.2...v0.9.3) - 2022-01-22

### Changed
- Breaking Change. Downgrade dbtoolbar to v0.9.4.


## [v0.9.2](https://github.com/nfaiz/ci4-ifx/compare/v0.9.1...v0.9.2) - 2021-08-21

### Enhancement

- Add placeholder name for bindings.
- Improved documentation.
- Add case insensitive parameter for `like()` method.
- Add `whereRaw()` and `orWhereRaw()` methods for non escaping where value.
- Add `selectMax()`, `selectMin()`, `selectSum()`, `selectCount()` and `selectAvg()` methods.

### Changed

- `numRows()` method to `affectedRows()`.


## [v0.9.1](https://github.com/nfaiz/ci4-ifx/compare/v0.9.0...v0.9.1) - 2021-08-17

### Enhancement

- Use CodeIgniter 4 database configuration style.

### Removed

- `PdoIfx` config file.


## [v0.9.0](https://github.com/nfaiz/ci4-ifx/releases/tag/v0.9.0) - 2021-08-16

### Added

- Initial pre-release.
