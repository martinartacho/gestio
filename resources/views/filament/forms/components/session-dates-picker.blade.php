@php
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <style>
        .sdp { display: flex; flex-direction: column; gap: .75rem; }
        .sdp-box { max-width: 20rem; border: 1px solid rgba(128,128,128,.35); border-radius: .5rem; padding: .75rem; }
        .sdp-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: .5rem; }
        .sdp-nav b { text-transform: capitalize; font-size: .875rem; font-weight: 500; }
        .sdp-nav button { padding: .1rem .6rem; border-radius: .25rem; font-size: 1.1rem; line-height: 1.4; cursor: pointer; background: none; border: 0; color: inherit; }
        .sdp-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: .25rem; text-align: center; }
        .sdp-dow { font-size: .75rem; opacity: .6; }
        .sdp-day { padding: .3rem 0; border-radius: .25rem; font-size: .875rem; cursor: pointer; background: none; border: 0; color: inherit; }
        .sdp-day:hover { background: rgba(128,128,128,.2); }
        .sdp-day.on, .sdp-day.on:hover { background: rgb(var(--primary-600, 217 119 6)); color: #fff; font-weight: 600; }
        .sdp-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
        .sdp-chip { display: inline-flex; align-items: center; gap: .3rem; padding: .15rem .6rem; border-radius: 999px; font-size: .75rem; background: rgba(128,128,128,.18); }
        .sdp-chip button { cursor: pointer; background: none; border: 0; color: inherit; padding: 0; font-size: .9rem; line-height: 1; }
        .sdp-count { font-size: .75rem; opacity: .7; margin: 0; }
    </style>

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
        class="sdp"
    >
        <div class="sdp-box">
            <div class="sdp-nav">
                <button type="button" x-on:click="move(-1)" aria-label="{{ __('Mes anterior') }}">‹</button>
                <b x-text="title()"></b>
                <button type="button" x-on:click="move(1)" aria-label="{{ __('Mes següent') }}">›</button>
            </div>

            <div class="sdp-grid sdp-dow">
                @foreach (['Dl', 'Dt', 'Dc', 'Dj', 'Dv', 'Ds', 'Dg'] as $dow)
                    <span>{{ $dow }}</span>
                @endforeach
            </div>

            <div class="sdp-grid" style="margin-top: .25rem">
                <template x-for="n in blanks()" :key="'b' + n"><span></span></template>
                <template x-for="d in days()" :key="d">
                    <button type="button" x-on:click="toggle(d)" x-text="d" :class="has(d) ? 'sdp-day on' : 'sdp-day'"></button>
                </template>
            </div>
        </div>

        <div class="sdp-chips">
            <template x-for="v in (dates || [])" :key="v">
                <span class="sdp-chip">
                    <span x-text="label(v)"></span>
                    <button type="button" x-on:click="remove(v)" aria-label="{{ __('Treure') }}">×</button>
                </span>
            </template>
        </div>

        <p class="sdp-count" x-text="(dates || []).length + ' {{ __('sessions marcades') }}'"></p>
    </div>
</x-dynamic-component>
