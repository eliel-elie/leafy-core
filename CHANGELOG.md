# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [1.2.8] - 2026-08-04

### Added
- Added database connection synchronization to Capsule when adding a new connection.

### Fixed
- Fixed Oracle database connection logic to use configuration values and handle missing prefix.

## [1.2.7] - 2026-06-20

### Added
- Integrated Pest PHP testing framework (`pestphp/pest`).
- Added comprehensive unit tests for core components (`Router`, `Response`, `Request`, `Config`, `Env`, `Session`, `Token`, `View`, `Header`, `FlashMessages`, and `Repository`).
- Added a basic feature test suite.

### Fixed
- Fixed session start behavior in `Session` to check if a session is already active before calling `session_start()`.

### Changed
- Configured composer test script to run `pest`.

## [1.2.6] - 2026-06-20


### Added
- Added language translation support.
- Implemented file-based logging system (`FileLogWriter`) with levels, custom formatting, cleanup, and rotation.
- Added a dedicated logging system README.
- Added `resource_path` helper function.
- Added `AuthorizesRequests` trait to Controllers for handling authorization and rendering 403 error views.
- Implemented support for multiple database connections.
- Added LDAP helper class for LDAP integration and authentication.

### Fixed
- Fixed default log path handling in `FileLogWriter`.
- Fixed URL path resolution and protocol detection when running the application in a subdirectory.
- Fixed various potential null value issues.

### Changed
- Refactored `AuthorizesRequests` trait to use the router when rendering the 403 view.
- Improved LDAP helper class code style and logic.
- Enforced return type declarations on `getMigrationPath` and `handle` methods.

## [1.1.0] - 2024-03-15
### Fixed
- Fixed issues when instantiating database/connection objects.

## [1.0.9] - 2024-02-10
### Changed
- Refactored core path root handling.

## [1.0.8] - 2024-01-20
### Added
- Added the `base_path` helper function.

## [1.0.7] - 2023-11-05
### Changed
- Updated the library to support Oracle database connections.

## [1.0.6] - 2023-09-12
### Changed
- Upgraded the package compatibility to support PHP 8.

## [1.0.5] - 2023-08-01
### Fixed
- Fixed run migration command execution.

## [1.0.4] - 2023-06-15
### Fixed
- Fixed default date and time formats.

## [1.0.3] - 2023-05-20
### Added
- Added automatic redirection to the previous URL after login.

## [1.0.2] - 2023-04-10
### Changed
- Defined the default session name.

## [1.0.1] - 2023-03-01
### Changed
- Updated `composer.json` requirements and metadata.

## [1.0.0] - 2023-02-15
### Added
- Initial release with core features.
- Added basic `README.md`.
