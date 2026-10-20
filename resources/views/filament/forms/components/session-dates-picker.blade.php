@php
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            dates: {{ $applyStateBindingModifiers("\$wire.\$entangle('{$statePath}')") }},
            cursor: null,
            disabled: @js($isDisabled()),
            init() {
                const first = (this.dates || []).slice().sort()[0];
                this.cursor = first ? new Date(first + 'T00:00:00') : new Date();
                this.cursor.setDate(1);
            },
            iso(y, m, d) {
                return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            },
            move(n) {
                this.cursor = new Date(this.cursor.getFullYear(), this.cursor.getMonth() + n, 1);
            },
            title() {
                return this.cursor.toLocaleDateString('ca', { month: 'long', year: 'numeric' });
            },
            blanks() {
                return (this.cursor.getDay() + 6) % 7;
            },
            days() {
                return new Date(this.cursor.getFullYear(), this.cursor.getMonth() + 1, 0).getDate();
            },
            has(d) {
                return (this.dates || []).includes(this.iso(this.cursor.getFullYear(), this.cursor.getMonth(), d));
            },
            toggle(d) {
                if (this.disabled) return;
                const v = this.iso(this.cursor.getFullYear(), this.cursor.getMonth(), d);
                const list = (this.dates || []).slice();
                const i = list.indexOf(v);
                i === -1 ? list.push(v) : list.splice(i, 1);
                this.dates = list.sort();
            },
            remove(v) {
                if (this.disabled) return;
                this.dates = (this.dates || []).filter(x => x !== v);
            },
            label(v) {
                return new Date(v + 'T00:00:00').toLocaleDateString('ca', { weekday: 'short', day: 'numeric', month: 'short' });
            },
        }"
        class="space-y-3"
    >
        <div class="max-w-xs rounded-lg border border-gray-200 p-3 dark:border-white/10">
            <div class="mb-2 flex items-center justify-between">
                <button type="button" class="rounded px-2 py-1 hover:bg-gray-100 dark:hover:bg-white/10" x-on:click="move(-1)" aria-label="{{ __('Mes anterior') }}">‹</button>
                <span class="text-sm font-medium capitalize" x-text="title()"></span>
                <button type="button" class="rounded px-2 py-1 hover:bg-gray-100 dark:hover:bg-white/10" x-on:click="move(1)" aria-label="{{ __('Mes següent') }}">›</button>
            </div>

            <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-500">
                @foreach (['Dl', 'Dt', 'Dc', 'Dj', 'Dv', 'Ds', 'Dg'] as $dow)
                    <span>{{ $dow }}</span>
                @endforeach
            </div>

            <div class="mt-1 grid grid-cols-7 gap-1 text-center text-sm">
                <template x-for="n in blanks()" :key="'b' + n"><span></span></template>
                <template x-for="d in days()" :key="d">
                    <button
                        type="button"
                        x-on:click="toggle(d)"
                        x-text="d"
                        class="rounded py-1"
                        :class="has(d) ? 'bg-primary-600 font-semibold text-white' : 'hover:bg-gray-100 dark:hover:bg-white/10'"
                    ></button>
                </template>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <template x-for="v in (dates || [])" :key="v">
                <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2 py-1 text-xs text-primary-700 dark:bg-primary-500/20 dark:text-primary-300">
                    <span x-text="label(v)"></span>
                    <button type="button" x-on:click="remove(v)" aria-label="{{ __('Treure') }}">×</button>
                </span>
            </template>
        </div>

        <p class="text-xs text-gray-500" x-text="(dates || []).length + ' {{ __('sessions marcades') }}'"></p>
    </div>
</x-dynamic-component>
