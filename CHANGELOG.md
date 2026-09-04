# Changelog

All notable changes to this project will be documented in this file, in reverse
chronological order by release.

## 4.0.0 - TBD

### Added

- Request attributes for the final correlation ID.
- Validated inbound ID handling and explicit accept or replace policies.
- Optional W3C traceparent version 00 trace-ID correlation.

### Changed

- Bump the minimum PHP version to 8.2.
- Generate UUID v4 values when an inbound ID is not accepted.

### Removed

- The allow_override and configurable UUID-version options.
- The legacy milestone-driven release workflow.

## 3.0.4 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.3 - 2023-09-19

3.0.x bugfix release.

## 2.2.0 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.1.0 - 2020-12-15

### Added

- GitHub Actions for phpcs, phpstan and release on milestone closed.
- Add support for UUID v6.
- Add support for PHP 8.0.

### Changed

- Bump minimum supported PHP version to 7.4.
