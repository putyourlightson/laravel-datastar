[![Stable Version](https://img.shields.io/packagist/v/putyourlightson/laravel-datastar?label=stable)]((https://packagist.org/packages/putyourlightson/laravel-datastar))
[![Total Downloads](https://img.shields.io/packagist/dt/putyourlightson/laravel-datastar)](https://packagist.org/packages/putyourlightson/laravel-datastar)

<p align="center"><img width="150" src="https://putyourlightson.com/assets/logos/datastar.svg"></p>

# Datastar Package for Laravel

### A view-driven, reactive hypermedia framework for Laravel.

> [!WARNING]
> **This package is in alpha and its API may change.**

This package integrates the [Datastar hypermedia framework](https://data-star.dev/) with [Laravel](https://laravel.com/), allowing you to create reactive front-ends driven by Blade views. It aims to replace the need for front-end frameworks such as React, Vue.js and Alpine.js + htmx, and instead lets you manage state and use logic within your Blade views.

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

Install manually using composer.

```shell
composer require putyourlightson/laravel-datastar:^1.0.0-alpha.1
```

## Usage

```html
<div data-signals-count="0">
    <div data-text="$count"></div>
    <button data-on-click="{{ datastar()->get('_datastar/increment') }}">
        <span id="button-text">Increment</span>
    </button>
</div>
```

```html
{{-- _datastar/increment.blade.php --}}

@mergesignals(['count' => $signals->count + 1])

@mergefragments
    <span id="button-text">
        Increment again
    </span>
@endmergefragments
```

### Datastar Helper

The `datastar()` helper function is available in Blade views and returns a `Datastar` helper that can be used to generate action requests to the Datastar controller.

#### `datastar()->get()`

Returns a `@get()` action request to render a view at the given path.

```html
{{ datastar()->get('_datastar/increment') }}
```

#### `datastar()->post()`

Works the same as [`datastar()->get()`](#datastar-get) but returns a `@post()` action request to render a view at the given path. A CSRF token is automatically generated and sent along with the request.

```html
{{ datastar()->post('_datastar/increment') }}
```

#### `datastar()->put()`

Works the same as [`datastar()->post()`](#datastar-post) but returns a `@put()` action request.

```html
{{ datastar()->put('_datastar/increment') }}
```

#### `datastar()->patch()`

Works the same as [`datastar()->post()`](#datastar-post) but returns a `@patch()` action request.

```html
{{ datastar()->patch('_datastar/increment') }}
```

#### `datastar()->delete()`

Works the same as [`datastar()->post()`](#datastar-post) but returns a `@delete()` action request.

```html
{{ datastar()->delete('_datastar/increment') }}
```

### Blade Directives

#### `@mergefragments`

Merges one or more fragments into the DOM.

```html
@mergefragments
    <div id="new-fragment">New fragment</div>
@endmergefragments
```

#### `@removefragments`

Removes one or more HTML fragments that match the provided selector from the DOM.

```html
@removefragments('#old-fragment')
```

#### `@mergesignals`

Updates the signals with new values.

```html
@mergesignals(['foo' => 1, 'bar' => 2])
```

#### `@removesignals`

Removes signals that match one or more provided paths.

```html
@removesignals(['foo', 'bar'])
```

#### `@executescript`

Executes JavaScript in the browser.

```html
@executescript
    alert('Hello, world!');
@endexecutescript
```

## Custom Controllers

You can send SSE events using a custom controller instead of a Blade view using the `DatastarEventStream` trait. Pass a callable into the `getStreamedResponse()` method and return the response.

```php
// routes/web.php

use App\Http\Controllers\CustomController;

Route::resource('/custom-controller', CustomController::class);
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Putyourlightson\Datastar\DatastarEventStream;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomController extends Controller
{
    use DatastarEventStream;

    public function index(): StreamedResponse
    {
        return $this->getStreamedResponse(function() {
            $signals = $this->getSignals();
            $this->mergeSignals(['count' => $signals->count + 1]);
            $this->mergeFragments('
                <span id="button-text">Increment again</span>
            ');
        });
    }
}
```

---

Created by [PutYourLightsOn](https://putyourlightson.com/).
