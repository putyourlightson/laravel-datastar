[![Stable Version](https://img.shields.io/packagist/v/putyourlightson/laravel-datastar?label=stable)]((https://packagist.org/packages/putyourlightson/laravel-datastar))
[![Total Downloads](https://img.shields.io/packagist/dt/putyourlightson/laravel-datastar)](https://packagist.org/packages/putyourlightson/laravel-datastar)

<p align="center"><img width="150" src="https://putyourlightson.com/assets/logos/datastar.svg"></p>

# Datastar Package for Laravel

### A view-driven, reactive hypermedia framework Laravel.

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

```bladehtml
<div data-signals-count="0">
    <div data-text="$count"></div>
    <button data-on-click="{{ $datastar->get('_datastar/increment) }}">
        <span id="button-text">Increment</span>
    </button>
</div>
```

```bladehtml
<!--_datastar/increment.blade.php-->
@mergesignals(['count' => $signals->count + 1])

@mergefragments
    <span id="button-text">
        Increment again
    </span>
@endmergefragments
```

---

Created by [PutYourLightsOn](https://putyourlightson.com/).
