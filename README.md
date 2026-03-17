# select-components

A small Laravel package providing reusable Blade components for select inputs.

This package is **designed for Livewire** and works well together with **Mary UI** (daisyUI + Tailwind UI components).

## Stack

This package is built using:

- Alpine.js
- Tailwind CSS
- daisyUI

## Requirements

- **Laravel 12+**
- **Livewire** (includes Alpine.js)
- **daisyUI** (Tailwind CSS plugin) or **Mary UI**

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

## Installation

### Install livewire

- Install: `composer require livewire/livewire`
- Docs: https://livewire.laravel.com/docs/4.x/installation

### Install **daisyUI** (Tailwind CSS plugin) or **Mary UI**

- Docs: https://daisyui.com/docs/install/laravel/
- Docs: https://mary-ui.com/docs/installation

### Install via Composer (Packagist)

```bash
composer require akhmads/select-components
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
    :initial_value="\App\Models\User::find($selected)"
/>
```

- `remote`: URL endpoint that returns a JSON array of option objects (e.g. `/api/users` or `{{ route('api.user') }}`).
- `option_value`: the key to use for the option value (default: `id`).
- `option_label`: the key to use for the option label (default: `name`).
- `placeholder`: placeholder text shown when nothing is selected.
- `clearable`: when present, allows clearing the selection.
- `initial_value`: (optional) can be either:
  - an array of IDs (will fetch labels from remote)
  - an array of objects `{id, name}` (will be used directly)

Add a simple endpoint to `routes/web.php` (or `routes/api.php`) that returns users in JSON:

```php
use Illuminate\Http\Request;

Route::get('/api/users', function (Request $request) {
     $search = $request->query('search');

     $users = \App\Models\User::query()
         ->when($search, function ($q) use ($search) {
             $q->where('name', 'like', "%{$search}%")
                 ->orWhere('email', 'like', "%{$search}%");
         })
         ->orderBy('name')
         ->limit(20)
         ->get();

     return $users;
})->name('api.users');
```

### Offline select

```blade
<x-offline-select
    wire:model="selected"
    :options="[['id' => 1, 'name' => 'Foo'], ['id' => 2, 'name' => 'Bar']]"
    option_value="id"
    option_label="name"
    placeholder="Select a user"
    clearable
    :initial_value="['id' => 2, 'name' => 'Bar']"
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
    :initial_value="\App\Models\User::whereIn([1,2])->get()"
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

### Contributing

To work on this package locally, clone the repo into a `packages` folder inside your Laravel project.

From your Laravel project root:

```bash
mkdir -p packages
cd packages
git clone https://github.com/akhmads/select-components.git
```

Then configure Composer to load it as a local package by adding a path repository to your `composer.json` (this is already configured in this workspace):

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

Finally, require the local package in your app:

```bash
composer require akhmads/select-components:*@dev --prefer-source
```

Once installed, you can make changes in `packages/select-components` and test them directly in your Laravel app.

---

## License

This package is released under the MIT License. See the official license text at https://opensource.org/licenses/MIT.
