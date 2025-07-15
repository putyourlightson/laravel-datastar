# Release Notes for Datastar

## 1.0.0-RC.1 - 2025-07-15

- The package now requires Datastar [1.0.0-RC.1](https://github.com/starfederation/datastar/releases/tag/v1.0.0-RC.1).
- Renamed the `fragments` Blade directive to `patchelements`.
- Renamed the `removefragments` Blade directive to `removeelements`.
- Renamed the `mergesignals` Blade directive to `patchsignals`.
- Renamed the `defaultFragmentOptions` config setting to `defaultElementOptions`.
- Removed the `removesignals` Blade directive.
- Removed the `datastar()->getFragments()` helper method.
- Removed the `SignalsModel` class. The `signals` variable passed into Datastar templates is now a regular array. Use the `patchsignals` Twig tag to update and remove signals.

## 1.0.0-beta.8 - 2025-04-15

### Added

- Added a `datastar()->getFragments()` helper method that fetches and merge fragments into the DOM.

## 1.0.0-beta.7 - 2025-04-09

### Changed

- Update the Datastar library to version 1.0.0-beta.11. 

## 1.0.0-beta.6 - 2025-03-01

### Changed

- Update the Datastar library to version 1.0.0-beta.9. 

## 1.0.0-beta.5 - 2025-02-25

### Changed

- Update the Datastar library to version 1.0.0-beta.8. 

## 1.0.0-beta.4 - 2025-02-14

### Changed

- Update the Datastar library to version 1.0.0-beta.7. 

## 1.0.0-beta.3 - 2025-02-12

### Changed

- Extract methods into the SSE service class.

## 1.0.0-beta.2 - 2025-02-02

### Added

- Added the `location` Blade directive.

## 1.0.0-beta.1 - 2025-01-13

- Initial beta release.
