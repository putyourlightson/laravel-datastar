[![Stable Version](https://img.shields.io/packagist/v/putyourlightson/laravel-datastar?label=stable)]((https://packagist.org/packages/putyourlightson/laravel-datastar))
[![Total Downloads](https://img.shields.io/packagist/dt/putyourlightson/laravel-datastar)](https://packagist.org/packages/putyourlightson/laravel-datastar)

<p align="center"><img width="150" src="https://putyourlightson.com/assets/logos/datastar.svg"></p>

# Datastar Package for Laravel

### A reactive hypermedia framework for Laravel.

> [!WARNING]
> Updating from the beta? View the [release notes](https://github.com/putyourlightson/laravel-datastar/blob/develop/CHANGELOG.md).

This package integrates the [Datastar hypermedia framework](https://data-star.dev/) with [Laravel](https://laravel.com/), allowing you to create reactive frontends driven by Blade views _or_ controllers. It aims to replace the need for front-end frameworks such as React, Vue.js and Alpine.js + htmx, and instead lets you manage state and use logic from your Laravel backend.

Use-cases:

- Live search and filtering
- Loading more elements / Infinite scroll
- Paginating, ordering and filtering lists
- Submitting forms and running actions
- Pretty much anything to do with reactive front-ends

## License

This package is licensed for free under the MIT License.

## Requirements

This package requires [Laravel](https://laravel.com/) 11.0.0 or later.

## Installation

Install manually using composer, then run the `artisan vendor:publish --tag=public` command to publish the public assets.

```shell
composer require putyourlightson/laravel-datastar:^1.0.0-RC.1

php artisan vendor:publish --tag=public
```

## Overview

The Datastar package for Laravel allows you to handle backend requests by sending SSE events using [Blade directives](#blade-directives) in views _or_ [using controllers](#using-controllers). The former requires less setup and is more straightforward, while the latter provides more flexibility.

Here’s a trivial example that toggles some backend state using the Blade view `datastar/toggle.blade.php` to handle the request.

```html
<div data-signals-enabled="false">
    <div data-text="$enabled ? 'ON' : 'OFF'"></div>
    <button data-on-click="{{ datastar()->get('datastar.toggle') }}">
        <span id="button-text">Enable</span>
    </button>
</div>
```

```php
{{-- datastar/toggle.blade.php --}}

@php
    $enabled = $signals['enabled'] ?? false;
@endphp

@patchsignals(['enabled' => $enabled])

@patchelements
    <span id="button-text">
        {{ $enabled ? 'Disable' : 'Enable' }}
    </span>
@endpatchelements
```

## Usage

Start by reading the [Getting Started](https://data-star.dev/guide/getting_started) guide to learn how to use Datastar on the frontend. The Datastar package for Laravel only handles backend requests.

> [!NOTE]
> The Datastar [VSCode extension](https://marketplace.visualstudio.com/items?itemName=starfederation.datastar-vscode) and [IntelliJ plugin](https://plugins.jetbrains.com/plugin/26072-datastar-support) have autocomplete for all `data-*` attributes.

When working with signals, note that you can convert a PHP array into a JSON object using the `json_encode` function.

```php
@php
    $signals = ['foo' => 1, 'bar' => 2];
@endphp

<div data-signals="{{ json_encode($signals) }}"></div>
```

### Datastar Helper

The `datastar()` helper function is available in Blade views and returns a `Datastar` helper that can be used to generate action requests to the Datastar controller. The Datastar controller renders a view containing one or [Blade directives](#blade-directives) that each send an SSE event. [Signals](#signals) are also sent as part of the request, and are made available in Datastar views using the `$signals` variable.

#### `datastar()->get()`

Returns a `@get()` action request to render a view at the given path. The value can be a file path _or_ a dot-separated path to a Blade view.

```php
{{ datastar()->get('path.to.view') }}
```

Variables can be passed into the view using a second argument. Any variables passed in will become available in the rendered view. Variables are tamper-proof yet visible in the source code in plain text, so you should avoid passing in any sensitive data.

```php
{{ datastar()->get('path.to.view', ['offset' => 10]) }}
```

#### `datastar()->post()`

Works the same as [`datastar()->get()`](#datastar-get) but returns a `@post()` action request to render a view at the given path. A CSRF token is automatically generated and sent along with the request.

```php
{{ datastar()->post('path.to.view') }}
```

#### `datastar()->put()`

Works the same as [`datastar()->post()`](#datastar-post) but returns a `@put()` action request.

```php
{{ datastar()->put('path.to.view') }}
```

#### `datastar()->patch()`

Works the same as [`datastar()->post()`](#datastar-post) but returns a `@patch()` action request.

```php
{{ datastar()->patch('path.to.view') }}
```

#### `datastar()->delete()`

Works the same as [`datastar()->post()`](#datastar-post) but returns a `@delete()` action request.

```php
{{ datastar()->delete('path.to.view') }}
```

```html
<div id="swap">The view should contain one or more elements to be patched.</div> 
```

### Blade Directives

#### `@patchelements`

Patches elements into the DOM.

```php
@patchelements
    <div id="new">New element</div>
@endpatchelements
```

#### `@removeelements`

Removes elements that match the provided selector from the DOM.

```php
@removeelements('#old')
```

#### `@patchsignals`

Patches signals into the frontend.

```php
@patchsignals(['foo' => 1, 'bar' => 2])
```

#### `@executescript`

Executes JavaScript in the browser.

```php
@executescript
    alert('Hello, world!');
@endexecutescript
```

#### `@location`

Redirects the browser by setting the location to the provided URI.

```php
@location('/guide')
```

### Using Controllers

You can send SSE events using your own controller _instead_ of the Datastar controller by using the `DatastarEventStream` trait. Return the `getStreamedResponse()` method, passing a callable into it that sends zero or more SSE events using methods provided.

```php
// routes/web.php

use App\Http\Controllers\MyController;

Route::resource('/my-controller', MyController::class);
```

```php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Putyourlightson\Datastar\DatastarEventStream;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyController extends Controller
{
    use DatastarEventStream;

    public function index(): StreamedResponse
    {
        return $this->getStreamedResponse(function() {
            $signals = $this->readSignals();
            $this->patchSignals(['enabled' => $signals->enabled ? false : true]);
            $this->patchElements('
                <span id="button-text">' . ($signals->enabled ? 'Enable' : 'Disable') . '</span>
            ');
        });
    }
}
```

### DatastarEventStream Trait

#### `patchElements()`

Patches elements into the DOM.

```php
$this->patchElements('<div id="new-element">New element</div>');
```

#### `removeElements()`

Removes elements that match the provided selector from the DOM.

```php
$this->removeElements('#old-element');
```

#### `patchSignals()`

Patches signals into the frontend.

```php
$this->patchSignals(['foo' => 1, 'bar' => 2]);
```

#### `executeScript()`

Executes JavaScript in the browser.

```php
$this->executeScript('alert("Hello, world!")');
```

#### `location()`

Redirects the browser by setting the location to the provided URI.

```php
$this->location('/guide');
```

#### `renderDatastarView()`

Renders a Datastar view.

```php
$this->renderDatastarView('datastar.toggle', ['enabled' => true]);
```

### Signals

Signals can be accessed within views rendered by Datastar using the signals variable, which is an array of signals received by the request that is automatically injected into the template.

```php
@php
    // Getting signal values.
    $username = $signals['username'];
@endphp
```

> [!NOTE]
> Signal patches _cannot_ be wrapped in `@patchelements` directives, since each update creates a server-sent event which will conflict with the element’s contents.

---

Created by [PutYourLightsOn](https://putyourlightson.com/).
