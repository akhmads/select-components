@props([
    'label' => null,
    'option_value' => 'id',
    'option_label' => 'name',
    'remote' => null,
    'initial_value' => null,
    'placeholder' => 'Select an option',
    'clearable' => false,
])

<fieldset class="fieldset py-0 w-full max-w-full min-w-0 {{ $attributes->get('class') }}">
    @if($label)
        <legend class="fieldset-legend mb-0.5">{{ $label }}</legend>
    @endif
<div
    wire:ignore
    x-data="{
        open: false,
        search: '',
        options: [],
        value: @entangle($attributes->wire('model')),
        selected: null,
        loading: false,
        highlightedIndex: 0,
        optionValue: '{{ $option_value }}',
        optionLabel: '{{ $option_label }}',
        remoteUrl: '{{ $remote }}',
        placeholder: '{{ $placeholder }}',

        init() {
            let initial = {{ json_encode($initial_value) }};
            if (this.value && initial) {
                this.selected = initial;
            }

            this.$watch('search', (value) => {
                if (this.remoteUrl && this.open) {
                    this.fetchOptions();
                }
            });

            this.$watch('value', (val) => {
                if (!val) {
                    this.selected = null;
                    return;
                }

                // If the value changes from the server, try to resolve its label from options.
                const found = this.options.find(o => o[this.optionValue] == val);
                if (found) {
                    this.selected = found;
                    return;
                }

                // If we don't have options yet, fetch them to resolve the label.
                if (this.remoteUrl && this.options.length === 0) {
                    this.fetchOptions();
                }
            });

            this.$watch('options', () => {
                if (this.selected && this.options.length > 0) {
                    const idx = this.options.findIndex(o => o[this.optionValue] == this.selected[this.optionValue]);
                    this.highlightedIndex = idx >= 0 ? idx : 0;
                } else {
                    this.highlightedIndex = 0;
                }
                this.scrollToHighlighted();
            });
        },

        toggle() {
            if (this.$root.hasAttribute('disabled')) return;
            this.open = !this.open;
            if (this.open) {
                setTimeout(() => {
                    this.$refs.searchInput.focus();
                    this.scrollToHighlighted();
                }, 50);
                if (this.options.length === 0 && this.remoteUrl) {
                    this.fetchOptions();
                }
            }
        },

        close() {
            this.open = false;
            this.highlightedIndex = 0;
            this.search = '';
        },

        moveHighlight(delta) {
            if (!this.options || this.options.length === 0) return;
            this.highlightedIndex = (this.highlightedIndex + delta + this.options.length) % this.options.length;
            this.scrollToHighlighted();
        },

        scrollToHighlighted() {
            if (!this.open) return;
            this.$nextTick(() => {
                const optionsList = this.$refs.optionsList;
                const dropdown = this.$refs.dropdown;
                if (!optionsList || !dropdown) return;

                if (this.highlightedIndex === 0) {
                    dropdown.scrollTop = 0;
                    return;
                }

                const options = optionsList.querySelectorAll('li');
                if (this.highlightedIndex >= options.length) {
                    this.highlightedIndex = 0;
                }
                const option = options[this.highlightedIndex];
                if (option) {
                    option.scrollIntoView({ block: 'nearest' });
                }
            });
        },

        fetchOptions() {
            this.loading = true;
            let url = new URL(this.remoteUrl);
            url.searchParams.append('search', this.search);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    this.options = data || [];
                    this.loading = false;
                    if (this.value && !this.selected) {
                        const found = this.options.find(o => o[this.optionValue] == this.value);
                        if (found) {
                            this.selected = found;
                        }
                    }
                })
                .catch(() => {
                    this.loading = false;
                    this.options = [];
                });
        },

        select(option) {
            this.selected = option;
            this.value = option[this.optionValue];
            this.close();
            this.$nextTick(() => {
                this.$refs.trigger.focus();
            });
        },

        clear() {
            if (this.$root.hasAttribute('disabled')) return;
            this.selected = null;
            this.value = null;
            this.search = '';
        }
    }"
    x-on:click.outside="close()"
    x-on:keydown.escape.window="close()"
    {{ $attributes->whereDoesntStartWith('wire:model')->except(['initial_value', 'initialValue', 'options', 'class', 'label'])->merge(['class' => 'relative']) }}
>
    {{-- Trigger --}}
    <div
        x-ref="trigger"
        x-on:click="if(! $root.hasAttribute('disabled')) toggle()"
        x-on:keydown.enter.prevent="if(! $root.hasAttribute('disabled')) toggle()"
        x-on:keydown.space.prevent="if(! $root.hasAttribute('disabled')) toggle()"
        x-on:keydown.down.prevent="if(! $root.hasAttribute('disabled')) toggle()"
        x-on:keydown.up.prevent="if(! $root.hasAttribute('disabled')) toggle()"
        @if($clearable)
        x-on:keydown.backspace.prevent="if(! $root.hasAttribute('disabled')) clear()"
        x-on:keydown.delete.prevent="if(! $root.hasAttribute('disabled')) clear()"
        @endif
        :tabindex="$root.hasAttribute('disabled') ? -1 : 0"
        :aria-disabled="$root.hasAttribute('disabled')"
        class="input input-bordered w-full grid grid-cols-[1fr_auto] items-center focus:outline-offset-2 focus:outline-2 focus:outline-primary min-w-0 max-w-full overflow-hidden"
        :class="{
            'input-disabled': $root.hasAttribute('disabled'),
            'bg-base-200': $root.hasAttribute('disabled'),
            'cursor-not-allowed': $root.hasAttribute('disabled'),
            'cursor-pointer': ! $root.hasAttribute('disabled'),
            'input-error': {{ $errors->has($attributes->wire('model')->value()) ? 'true' : 'false' }}
        }"
    >
        <span x-text="selected ? selected[optionLabel] : placeholder" class="truncate block flex-1 min-w-0 max-w-full" :class="{'text-base-content/50': !selected}"></span>

        <div class="flex items-center gap-2">
            {{-- Clear Button --}}
            @if($clearable)
            <button
                x-show="selected && ! $root.hasAttribute('disabled')"
                x-on:click.stop="clear()"
                type="button"
                class="btn btn-ghost btn-xs btn-circle text-base-content/50 hover:text-base-content"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            @endif

            {{-- Chevron --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </div>
    </div>

    {{-- Dropdown --}}
    <div
        x-ref="dropdown"
        x-anchor.bottom-start="$refs.trigger"
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-box shadow-lg max-h-60 overflow-auto flex flex-col"
        style="display: none;"
    >
        {{-- Search Input --}}
        <div class="p-2 sticky top-0 bg-base-100 z-10 border-b border-base-200">
            <input
                x-ref="searchInput"
                x-model.debounce.300ms="search"
                x-on:keydown.down.stop.prevent="moveHighlight(1)"
                x-on:keydown.up.stop.prevent="moveHighlight(-1)"
                x-on:keydown.enter.stop.prevent="if(options.length > 0) select(options[highlightedIndex])"
                x-on:keydown.escape.stop.prevent="close()"
                x-on:keydown.tab="close()"
                type="text"
                class="input input-sm input-bordered w-full"
                placeholder="Search..."
            >
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="p-4 text-center text-sm text-base-content/50">
            <span class="loading loading-spinner loading-sm"></span> Loading...
        </div>

        {{-- Options --}}
        <ul x-ref="optionsList" x-show="!loading && options.length > 0" class="menu menu-sm w-full p-0">
            <template x-for="(option, index) in options" :key="option[optionValue]">
                <li>
                    <a
                        x-on:click="select(option)"
                        x-on:mouseenter="highlightedIndex = index"
                        :class="{'bg-primary text-primary-content': index === highlightedIndex, 'bg-base-200': selected && selected[optionValue] == option[optionValue]}"
                        class="text-sm rounded-none"
                    >
                        <span x-text="option[optionLabel]" class="truncate block"></span>
                    </a>
                </li>
            </template>
        </ul>
        <div x-show="!loading && options.length === 0" class="p-4 text-sm text-center text-base-content/50">
            No results found.
        </div>
    </div>
</div>
    @error($attributes->wire('model')->value())
        <span class="fieldset-label text-error">{{ $message }}</span>
    @enderror
</fieldset>
