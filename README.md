[![Stable Version](https://img.shields.io/packagist/v/putyourlightson/laravel-datastar?label=stable)]((https://packagist.org/packages/putyourlightson/laravel-datastar))
[![Total Downloads](https://img.shields.io/packagist/dt/putyourlightson/laravel-datastar)](https://packagist.org/packages/putyourlightson/laravel-datastar)

<p align="center"><img width="150" src="https://putyourlightson.com/assets/logos/datastar.svg"></p>

# Datastar Package for Laravel

### A reactive hypermedia framework for Laravel.

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
composer require putyourlightson/laravel-datastar:^1.0.0

php artisan vendor:publish --tag=public
```

## Overview

The Datastar package for Laravel allows you to handle backend requests by sending SSE events using [Blade directives](#blade-directives) in views _or_ [using controllers](#using-controllers). The former requires less setup and is more straightforward, while the latter provides more flexibility.

Here’s a trivial example that toggles some backend state using the Blade view `datastar/toggle.blade.php` to handle the request.

```blade
{{-- main.blade.php --}}

<div data-signals:enabled="false">
    <div data-text="$enabled ? 'ON' : 'OFF'"></div>
    <button data-on:click="{{ datastar()->view('datastar.toggle') }}">
        <span id="button-text">Enable</span>
    </button>
</div>
```

```blade
{{-- datastar/toggle.blade.php --}}

@php
    $enabled = $signals['enabled'] ?? false;
@endphp

@patchsignals(['enabled' => !$enabled])

@patchelements
    <span id="button-text">
        {{ $enabled ? 'Enable' : 'Disable' }}
    </span>
@endpatchelements
```

## Usage

Start by reading the [Getting Started](https://data-star.dev/guide/getting_started) guide to learn how to use Datastar on the frontend. The Datastar package for Laravel only handles backend requests.

> [!TIP]
> The Datastar [VSCode extension](https://marketplace.visualstudio.com/items?itemName=starfederation.datastar-vscode) and [IntelliJ plugin](https://plugins.jetbrains.com/plugin/26072-datastar-support) have autocomplete for all `data-*` attributes.

When working with signals, note that you can convert a PHP array into a JSON object using the `json_encode` function.

```blade
{{-- main.blade.php --}}

@php
    $signals = ['foo' => 1, 'bar' => 2];
@endphp

<div data-signals="{{ json_encode($signals) }}"></div>
```

### Datastar Helper

The `datastar()` helper function is available in Blade views and returns a `Datastar` helper that can be used to generate action requests to the Datastar controller. The Datastar controller can either render a view, run a controller action, or call a route, each of which respond by sending an event stream containing zero or more SSE events. 

[Signals](#signals) are also sent as part of the request, and are made available in Datastar views using the `$signals` variable.

#### `datastar()->view()`

Returns a `@get()` action request to render a view. The value should be a dot-separated path to a Blade view.

```blade
// Sends a `GET` request that renders a Blade view
{{ datastar()->view('path.to.view') }}
```

Variables can be passed in as a second argument, that will be available in the rendered view.

> [!WARNING]
> Variables are tamper-proof yet visible in the source code in plain text, so you should avoid passing in any sensitive data.
> Only primitive data types can be used as variables: **strings**, **numbers**, **booleans** and **arrays**. Objects and models _cannot_ be used.

```blade
// Sends a `GET` request that renders a Blade view
{{ datastar()->view('path.to.view', ['foo' => 'bar']) }}
```

Options can be passed into the `@get()` action using a third argument. 

```blade
// Sends a `GET` request that renders a Blade view
{{ datastar()->view('path.to.view', ['foo' => 'bar'], ['contentType' => 'form']) }}
```

#### `datastar()->action()`

Returns a `@post()` action request to run a controller action. The value should be an array with a controller class name as the first value and an action name as the second. A CSRF token is automatically generated and sent along with the request.

```blade
// Sends a `POST` request that runs a controller action
{{ datastar()->action(['MyController', 'update']) }}
```

Params can be passed in as a second argument, that will be available as arguments to the controller action

> [!WARNING]
> Params are tamper-proof yet visible in the source code in plain text, so you should avoid passing in any sensitive data.
> Only primitive data types can be used as params: **strings**, **numbers**, **booleans** and **arrays**. Objects and models _cannot_ be used. Route-model binding works with controller actions.

```blade
// Sends a `POST` request that runs a controller action
{{ datastar()->action(['MyController', 'update'], ['foo' => 'bar']) }}
```

Options can be passed into the `@get()` action using a third argument. 

```blade
// Sends a `POST` request that runs a controller action
{{ datastar()->action(['MyController', 'update'], ['foo' => 'bar'], ['contentType' => 'form']) }}
```

#### `datastar()->get()`

Returns a `@get()` action request that calls a route. The value must be a defined route.

```blade
// Sends a `GET` request to a route
{{ datastar()->get('/uri') }}
```

Options can be passed into the `@get()` action using a second argument. 

```blade
// Sends a `GET` request to a route
{{ datastar()->get('/uri', ['contentType' => 'form']) }}
```

#### `datastar()->post()`

Works the same as [`datastar()->get()`](#datastar-get) but returns a `@post()` action request that calls a route. A CSRF token is automatically generated and sent along with the request.

```blade
// Sends a `POST` request to a route
{{ datastar()->post('/uri') }}
```

#### `datastar()->put()`

Works the same as [`datastar()->post()`](#datastar-post) but returns a `@put()` action request that calls a route.

```blade
// Sends a `PUT` request to a route
{{ datastar()->put('/uri') }}
```

#### `datastar()->patch()`

Works the same as [`datastar()->post()`](#datastar-post) but returns a `@patch()` action request that calls a route.

```blade
// Sends a `PATCH` request to a route
{{ datastar()->patch('/uri') }}
```

#### `datastar()->delete()`

Works the same as [`datastar()->post()`](#datastar-post) but returns a `@delete()` action request that calls a route.

```blade
// Sends a `DELETE` request to a route
{{ datastar()->delete('/uri') }}
```

### Blade Directives

Datastar Blade directives can patch and remove elements, patch signals, execute JavaScript and redirect the browser.

#### Patch Elements

The `@patchelements` directive allows you to [patch elements](https://data-star.dev/guide/getting_started#patching-elements) into the DOM.

```blade
{{-- main.blade.php --}}

<div id="results"></div>

<div id="search">
    <button data-on:click="{{ datastar()->view('datastar.search') }}">
        Search
    </button>
</div>
```

```blade
{{-- datastar/search.blade.php --}}

@patchelements
    <div id="results">
        ...
    </div>
@endpatchelements

@patchelements
    <div id="search">
        Search complete!
    </div>
@endpatchelements
```

This will swap the elements with the IDs `results` and `search` into the DOM. Note that elements with those IDs **must** already exist in the DOM, unless a mode is specified (see below).

##### Element Patch Options

Elements are patched into the DOM based on element IDs, by default. It’s possible to pass other modes and other [element patch options](https://data-star.dev/reference/sse_events#datastar-patch-elements) in as an argument.

```blade
@patchelements(['selector' => '#list', 'mode' => 'append'])
    <li>A new list item</li>
@endpatchelements
```

##### Automatic Element Patching

Any elements output in a Datastar template (outside any `@patchelements` tags) will be automatically wrapped in a `@patchelements` directive. This makes it possible to write your views in a way that makes them more reusable.

```blade
{{-- datastar/search.blade.php --}}

<div id="results"></div>
```

The view above is the equivalent of writing:

```blade
{{-- datastar/search.blade.php --}}

@patchelements
    <div id="results"></div>
@endpatchelements
```

While automatic element patching is convenient, it is less explicit and more restrictive (since [element patch options](#element-patch-options) cannot be used), so should only be used when appropriate.

#### Remove Elements

Elements can be removed from the DOM using the `@removeelements` directive, which accepts a CSS selector.

```blade
@removeelements('#list')
```

#### Patch Signals

The `@patchsignals` directive allows you to [patch signals](https://data-star.dev/guide/reactive_signals#patching-signals) into the frontend signals.

> [!NOTE]
> Signals patches **cannot** be wrapped in `@patchelements` directives, since each patch creates a server-sent event which will conflict with the element’s contents.

```blade
{{- Sets the value of the `username` signal. -}}
@patchsignals(['username' => 'johnny'])

{{- Sets multiple signal values using an array of key-value pairs. -}}
@patchsignals(['username' => 'bobby', 'success' => true])

{{- Removes the `username` signal by setting it to `null`. -}}
@patchsignals(['username' => null])
```

#### Signal Patch Options

It’s possible to pass [signal patch options](https://data-star.dev/reference/sse_events#datastar-patch-signals) in as a second argument.

```blade
@patchsignals(['username' => 'johnny'], ['onlyIfMissing' => true])
```

### Executing JavaScript

The `@executescript` directive allows you to send JavaScript to the browser to be executed on the front-end.

```blade
@executescript
    alert('Username is valid');
@endexecutescript
```

#### Execute Script Options

It’s possible to pass execute script options in as an argument. They are applied to the `<script>` tag that is appended to the DOM.

```blade
@executescript(['autoRemove' => true, 'attributes' => ['defer' => true]])
    alert('Username is valid');
@endexecutescript
```

### Redirecting

The `@location` directive allows you to redirect the page by updating `window.location` on the front-end.

```blade
@location('/guide')
```

#### Location Options

It’s possible to pass location options in as a second argument. They are applied to the `<script>` tag that is appended to the DOM.

```blade
@location('/guide', ['autoRemove' => true, 'attributes' => ['defer' => true]])
```

### Using Controllers

You can send SSE events from your own controller using the `sse()` helper. No routes are required, as Datastar will handle routing to the controller action you specify when using the [Datastar helper](#datastar-helper).

```blade
{{-- main.blade.php --}}

// Sends a `POST` request that runs a controller action
{{ datastar()->action(['MyController', 'update']) }}
```

```php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyController extends Controller
{
    public function index(): StreamedResponse
    {
        $signals = sse()->readSignals();

        $enabled = $signals['enabled'] ?? false;

        sse()->patchSignals(['enabled' => !$enabled]);

        sse()->patchElements('
            <span id="button-text">' . ($enabled ? 'Enable' : 'Disable') . '</span>
        ');
        
        return sse()->getEventStream();
    }
    
    public function view(): StreamedResponse
    {
        sse()->renderView('path.to.view');
        
        return sse()->getEventStream();
    }
}
```

Controller actions _must_ return a `StreamedResponse` created using the `getEventStream()` method.

### `sse()` Helper

The `sse()` helper provides methods to patch elements, patch signals, execute scripts, redirect the browser and render Datastar views.

#### `patchElements()`

Patches elements into the DOM. Accepts [element patch options](#element-patch-options) as an optional second argument.

```php
sse()->patchElements('<div id="new-element">New element</div>');
```

#### `removeElements()`

Removes elements that match the provided selector from the DOM.

```php
sse()->removeElements('#list');
```

#### `patchSignals()`

Patches signals into the frontend. Accepts [signal patch options](#signal-patch-options) as an optional second argument.

```php
sse()->patchSignals(['foo' => 1, 'bar' => 2]);
```

#### `executeScript()`

Executes JavaScript in the browser. Accepts [execute script options](#execute-script-options) as an optional second argument, which are applied to the `<script>` tag that is appended to the DOM.

```php
sse()->executeScript('alert("Hello, world!")');
```

#### `location()`

Redirects the browser by setting the location to the provided URI. Accepts [location options](#location-options) as an optional second argument, which are applied to the `<script>` tag that is appended to the DOM.

```php
sse()->location('/guide');
```

#### `renderView()`

Renders a Datastar view. Accepts the view path as the first argument and an optional array of variables as the second argument. The Blade view should output Datastar directives.

```php
sse()->renderView('datastar.toggle', ['enabled' => true]);
```

### Signals

Signals can be accessed within views rendered by Datastar using the signals variable, which is an array of signals received by the request that is automatically injected into the template.

> [!NOTE]
> Signals patches **cannot** be wrapped in `@patchelements` directives, since each patch creates a server-sent event which will conflict with the element’s contents.

```blade
<input data-bind:username>
<button data-on:click="{{ datastar()->get('path.to.view') }}">
    Check
</button>
```

```blade
@php
    $username = $signals['username'];
@endphp
```

If you ever need to read the signals in a request that is *not* handled by the Datastar package, you can do so as follows.

```php
@php
    $signals = sse()->readSignals();
@endphp
```

---

Created by [PutYourLightsOn](https://putyourlightson.com/).
