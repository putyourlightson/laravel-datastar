[![Stable Version](https://img.shields.io/packagist/v/putyourlightson/laravel-datastar?label=stable)]((https://packagist.org/packages/putyourlightson/laravel-datastar))
[![Total Downloads](https://img.shields.io/packagist/dt/putyourlightson/laravel-datastar)](https://packagist.org/packages/putyourlightson/laravel-datastar)

<p align="center"><img width="150" src="https://putyourlightson.com/assets/logos/datastar.svg"></p>

# Datastar Package for Laravel

### A reactive hypermedia framework for Laravel.

> [!WARNING]
> **This package is in beta and its API may change.**

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
composer require putyourlightson/laravel-datastar:^1.0.0-beta.1

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
    $enabled = $signals->enabled;
    // Do something with the state and toggle the enabled state.
    $enabled = !$enabled;
@endphp

@mergesignals(['enabled' => $enabled])

@mergefragments
    <span id="button-text">
        {{ $enabled ? 'Disable' : 'Enable' }}
    </span>
@endmergefragments
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

### Blade Directives

#### `@mergefragments`

Merges one or more fragments into the DOM.

```php
@mergefragments
    <div id="new-fragment">New fragment</div>
@endmergefragments
```

#### `@removefragments`

Removes one or more HTML fragments that match the provided selector from the DOM.

```php
@removefragments('#old-fragment')
```

#### `@mergesignals`

Updates the signals with new values.

```php
@mergesignals(['foo' => 1, 'bar' => 2])
```

#### `@removesignals`

Removes signals that match one or more provided paths.

```php
@removesignals(['foo', 'bar'])
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
<?php

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
            $signals = $this->getSignals();
            $this->mergeSignals(['enabled' => $signals->enabled ? false : true]);
            $this->mergeFragments('
                <span id="button-text">' . ($signals->enabled ? 'Enable' : 'Disable') . '</span>
            ');
        });
    }
}
```

### DatastarEventStream Trait

#### `mergeFragments()`

Merges one or more fragments into the DOM.

```php
$this->mergeFragments('<div id="new-fragment">New fragment</div>');
```

#### `removeFragments()`

Removes one or more HTML fragments that match the provided selector from the DOM.

```php
$this->removeFragments('#old-fragment');
```

#### `mergeSignals()`

Updates the signals with new values.

```php
$this->mergeSignals(['foo' => 1, 'bar' => 2]);
```

#### `removeSignals()`

Removes signals that match one or more provided paths.

```php
$this->removeSignals(['foo', 'bar']);
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

### Signals

When working with signals, either in views rendered by the Datastar controller or by calling `$this->getSignals()`, you are working with a [Signals model](https://github.com/putyourlightson/laravel-datastar/blob/develop/src/Models/Signals.php), which provides a simple way to manage signals.

```php
@php
    // Getting signal values.
    $username = $signals->username;
    $username = $signals->get('username');
    $username = $signals->get('user.username');
    
    // Setting signal values.
    $username = $signals->username('bobby');
    $username = $signals->set('username', 'bobby');
    $username = $signals->set('user.username', 'bobby');
    $username = $signals->setValues(['user.username' => 'bobby', 'success' => true]);
    
    // Removing signal values.
    $username = $signals->remove('username');
    $username = $signals->remove('user.username');
@endphp
```

> [!NOTE]
> Signals updates _cannot_ be wrapped in `{% mergefragment %}` tags, since each update creates a server-sent event which will conflict with the fragment’s contents.

---

Created by [PutYourLightsOn](https://putyourlightson.com/).
