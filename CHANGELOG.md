# Release Notes for Datastar

## 1.0.0-RC.6 - 2025-08-23

- Improved error handling.
- Improved handling of calls to `dump()` and `dd()`.

## 1.0.0-RC.5 - 2025-08-17

- The package now includes Datastar [1.0.0-RC.5](https://github.com/starfederation/datastar/releases/tag/v1.0.0-RC.5).
- Fixed a bug in which options were being double JSON encoded.

## 1.0.0-RC.4 - 2025-08-12

- Made it possible to pass options in as string, for cases when JSON encoding is not desirable.
- Improved error handling.

## 1.0.0-RC.3 - 2025-08-09

- The package now includes Datastar [1.0.0-RC.4](https://github.com/starfederation/datastar/releases/tag/v1.0.0-RC.4).
- The `datastar()->get()` and equivalent Blade directives now only support passing a route URI.
- Added the ability to render views in backend requests by passing a view path `path.to.view` to `datastar()->view()`.
- Added the ability to pass controller actions to backend requests by passing an array `['MyController', 'myAction']` to `datastar()->action()`.
- Added the `getValidator()` method to the `sse()` helper.
- Added the `validate()` and `validateWithBag()` methods to the `sse()` helper.
- Added the `shouldCloseSession()` method to the `sse()` helper that determines whether the session should be closed when the event stream begins.
- The session is now closed by default when the `getEventStream()` method is called, to prevent session locking.
- Replaced the `DatastarEventStream` trait with the `sse()` helper.
- Renamed the `getStreamedResponse()` method to `getEventStream()`.
- Renamed the `renderDatastarView()` method to `renderView()`.

## 1.0.0-RC.2 - 2025-07-17

- The package now includes Datastar [1.0.0-RC.2](https://github.com/starfederation/datastar/releases/tag/v1.0.0-RC.2).

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
