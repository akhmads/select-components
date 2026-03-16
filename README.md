# select-components

A small Laravel package providing reusable Blade components for select inputs.

This package is **designed for Livewire** and works well together with **Mary UI** (daisyUI + Tailwind UI components).

## Stack

This package is built using:

- Alpine.js
- Tailwind CSS
- daisyUI

## Features

- Responsive design
- Dark mode support
- Adaptive dropdowns (auto width, truncation)
- Keyboard navigation support (arrow keys + enter)
- Built for Livewire (`wire:model` friendly)
- Clearable selection + optional placeholder
- Supports disabled state + focus management
- Initial value support (pre-selected values)
- Works well with Mary UI / daisyUI styling

## Included components

- **`<x-remote-select />`**
    - Fetches options via AJAX (remote endpoint)
    - Supports searching/filtering as you type
    - Keeps label/value in sync when `wire:model` changes

- **`<x-offline-select />`**
    - Uses a local `options` array (static data)
    - Supports searching locally

- **`<x-multi-select />`**
    - Supports selecting multiple values
    - Uses the same remote (AJAX) mechanism as `remote-select`

## Installation (local development)

This repository is meant to be used as a local package. In the root Laravel project `composer.json`, add a path repository (this is already configured in this workspace):

```json
"repositories": [
  {
    "type": "path",
    "url": "packages/select-components",
    "options": {
      "symlink": true
    }
  }
]
```

Then install via Composer:

```bash
composer require select/select-components:*@dev
```

## Usage

### Remote select

```blade
<x-remote-select
    wire:model="selected"
    remote="/api/users"
    option_value="id"
    option_label="label"
    placeholder="Select a user"
    clearable
/>
```

- `remote`: URL endpoint that returns JSON array of options.
- `option_value` / `option_label`: which keys to use for value/label.
- `initial_value`: (optional) can be either:
    - an array of IDs (will fetch labels from remote)
    - an array of objects `{id, label}` (will be used directly)

### Offline select

```blade
<x-offline-select
    wire:model="selected"
    :options="[['id' => 1, 'name' => 'Foo'], ['id' => 2, 'name' => 'Bar']]"
    option_value="id"
    option_label="name"
    placeholder="Select a user"
    clearable
/>
```

### Multi select

```blade
<x-multi-select
    wire:model="selected"
    remote="/api/users"
    option_value="id"
    option_label="label"
    placeholder="Select users"
    clearable
/>
```

## Overriding views (optional)

To customize the component Blade templates, publish the views:

```bash
php artisan vendor:publish --tag=views --provider="SelectComponents\SelectComponentsServiceProvider"
```

## Notes

- The components are registered automatically via package discovery.
- `remote-select` and `multi-select` rely on a JSON endpoint returning an array of objects. Each object should have an `id` (or your chosen key) and a label key.
