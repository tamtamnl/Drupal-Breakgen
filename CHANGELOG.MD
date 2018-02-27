# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Added hooks for 3rd party extensions.
- Added example module "breakgen_crop" for 3rd party integration.
- Added missing PHP doc annotations.
- Added command to clear all breakgen related entities.
- Added post save hook for 3rd party extensions.

### Fixed
- Fixed warning when percentages are not set on breakpoint.
- Fixed crop_type definition having "x" instead of ":" for aspect ratio

## 0.0.1 - 2014-05-31
### Added
- Ability to generate image styles from a breakpoint file.
- This CHANGELOG file to hopefully serve as an evolving example of a
  standardized open source project CHANGELOG.
- Standard breakpoint file.
- README for basic instructions.

[Unreleased]: https://github.com/tamtamnl/Drupal-Breakgen/compare/v0.0.1...HEAD