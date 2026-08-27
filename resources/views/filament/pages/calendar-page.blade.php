<x-filament-panels::page>

    {{-- ── Barra de controls ──────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">

        {{-- Selector de període --}}
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('site.season') }}:
            </span>
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="currentSeasonId">
                    @foreach($this->getSeasonOptions() as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        {{-- Mode de vista --}}
        <div style="display:inline-flex; align-items:center; border:1px solid #d1d5db; border-radius:0.5rem; overflow:hidden;">
            <button type="button" wire:click="$set('viewMode','month')"
                    style="padding:0.375rem 0.75rem; font-size:0.875rem; font-weight:500; border:none; cursor:pointer; background:{{ $viewMode === 'month' ? '#4f46e5' : '#fff' }}; color:{{ $viewMode === 'month' ? '#fff' : '#4b5563' }};">
                Mensual
            </button>
            <button type="button" wire:click="$set('viewMode','week')"
                    style="padding:0.375rem 0.75rem; font-size:0.875rem; font-weight:500; border:none; border-left:1px solid #d1d5db; cursor:pointer; background:{{ $viewMode === 'week' ? '#4f46e5' : '#fff' }}; color:{{ $viewMode === 'week' ? '#fff' : '#4b5563' }};">
                Setmanal
            </button>
        </div>

{{-- Botons dreta --}}
        <div class="ml-auto flex items-center gap-2">

            {{-- Vista prèvia catàleg --}}
            <x-filament::button
                tag="a"
                :href="$currentSeasonId
                    ? route('campus.catalog.index', ['season' => $currentSeasonId, 'preview' => 1])
                    : route('campus.catalog.index', ['preview' => 1])"
                target="_blank"
                color="info"
                size="sm"
                icon="heroicon-o-eye"
            >
                Vista prèvia
            </x-filament::button>

            {{-- Exportació WooCommerce --}}
            <x-filament::button
                tag="a"
                :href="route('calendar.export.woocommerce', ['season' => $currentSeasonId])"
                color="gray"
                size="sm"
                icon="heroicon-o-arrow-down-tray"
            >
                {{ __('site.export_wp') }}
            </x-filament::button>

            {{-- Llegenda de colors (popover ⓘ) --}}
            @php $legend = $this->getLegendCategories(); @endphp
            @if ($legend->isNotEmpty())
            <div x-data="{ open: false }" style="position:relative; display:inline-flex; align-items:center;" @keydown.escape.window="open = false">
                <button
                    @click="open = !open"
                    title="Llegenda de colors"
                    style="display:inline-flex; align-items:center; justify-content:center; width:1.5rem; height:1.5rem; border-radius:9999px; border:none; background:transparent; cursor:pointer; color:#9ca3af; flex-shrink:0;"
                    onmouseover="this.style.color='#6b7280'" onmouseout="this.style.color='#9ca3af'"
                >
                    <svg style="width:1.1rem; height:1.1rem;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-transition
                    @click.outside="open = false"
                    style="display:none; position:absolute; right:0; top:1.8rem; z-index:30; width:15rem; background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; box-shadow:0 10px 15px -3px rgb(0 0 0/.1); padding:0.75rem;"
                >
                    <p style="font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin-bottom:0.5rem;">Colors per categoria</p>
                    @foreach ($legend as $item)
                        <div style="display:flex; align-items:center; gap:0.6rem; padding:0.25rem 0;">
                            <span style="width:0.7rem; height:0.7rem; border-radius:9999px; flex-shrink:0; background-color:{{ $item['color'] }}"></span>
                            <span style="font-size:0.8rem; color:#374151;">{{ $item['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

    </div>

    {{-- ── Contenidor FullCalendar ─────────────────────────────────────── --}}
    @if ($viewMode === 'month')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 overflow-x-auto">
        <div id="campus-calendar"></div>
    </div>
    @endif

    {{-- ── Graella d'horari setmanal recurrent ─────────────────────────── --}}
    @if ($viewMode === 'week')
    @php $grid = $this->getWeeklyGrid(); $days = $this->getDayNames(); @endphp
    <div style="background:#fff; border-radius:0.75rem; box-shadow:0 1px 3px 0 rgb(0 0 0/.1); padding:1rem; overflow-x:auto;">
        @if (empty($grid))
            <p style="font-size:0.875rem; color:#9ca3af; text-align:center; padding:2.5rem 0;">Cap curs amb franja horària assignada per a aquesta temporada.</p>
        @else
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr>
                    <th style="text-align:left; font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; padding:0.5rem; width:6rem;">Hora</th>
                    @foreach ($days as $day)
                        <th style="text-align:left; font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; padding:0.5rem; border-left:1px solid #f3f4f6;">
                            {{ $day }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($grid as $row)
                    <tr style="border-top:1px solid #f3f4f6; vertical-align:top;">
                        <td style="padding:0.5rem; font-size:0.75rem; font-weight:500; color:#6b7280; white-space:nowrap; vertical-align:top;">
                            {{ substr($row['start'], 0, 5) }}–{{ substr($row['end'], 0, 5) }}
                        </td>
                        @foreach ($row['days'] as $dayCourses)
                            <td style="padding:0.5rem; border-left:1px solid #f3f4f6; vertical-align:top; min-width:9rem;">
                                @foreach ($dayCourses as $course)
                                    @php $courseColor = $this->colorHex($course->category?->color); @endphp
                                    <button type="button" wire:click="openCourseModal({{ $course->id }})"
                                            style="display:block; width:100%; text-align:left; border:none; cursor:pointer; border-radius:0.375rem; padding:0.25rem 0.5rem; margin-bottom:0.25rem; font-size:0.75rem; background-color:{{ $courseColor }}1a; color:{{ $courseColor }};">
                                        <span style="font-weight:600;">{{ $course->code ?: $course->title }}</span>
                                        @if ($course->space)
                                            <span style="display:block; font-size:0.7rem; opacity:.75;">{{ $course->space->name }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    {{-- ── Modal detall del curs ───────────────────────────────────────── --}}
    @if($showCourseModal)
        @php $course = $this->getSelectedCourse(); @endphp
        <div
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            x-data
            @click.self="$wire.closeCourseModal()"
        >
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-lg">

                {{-- Capçalera --}}
                <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $course?->title ?? __('site.course') }}
                    </h3>
                    <button
                        wire:click="closeCourseModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Cos --}}
                @if($course)
                    <div class="px-6 py-4 space-y-3 text-sm text-gray-700 dark:text-gray-300">

                        @if($course->code)
                            <div class="flex gap-2">
                                <span class="font-medium w-28 shrink-0">{{ __('site.course_code') }}:</span>
                                <span class="font-mono">{{ $course->code }}</span>
                            </div>
                        @endif

                        <div class="flex gap-2">
                            <span class="font-medium w-28 shrink-0">{{ __('site.season') }}:</span>
                            <span>{{ $course->season?->name }}</span>
                        </div>

                        <div class="flex gap-2">
                            <span class="font-medium w-28 shrink-0">{{ __('site.course_format') }}:</span>
                            <span>{{ __('site.formats.' . $course->format) }}</span>
                        </div>

                        <div class="flex gap-2">
                            <span class="font-medium w-28 shrink-0">{{ __('site.course_start') }}:</span>
                            <span>{{ $course->start_date?->format('d/m/Y') }}</span>
                        </div>

                        <div class="flex gap-2">
                            <span class="font-medium w-28 shrink-0">{{ __('site.course_end') }}:</span>
                            <span>{{ $course->end_date?->format('d/m/Y') }}</span>
                        </div>

                        @if($course->sessions)
                            <div class="flex gap-2">
                                <span class="font-medium w-28 shrink-0">{{ __('site.course_sessions') }}:</span>
                                <span>{{ $course->sessions }}</span>
                            </div>
                        @endif

                        @if($course->space)
                            <div class="flex gap-2">
                                <span class="font-medium w-28 shrink-0">{{ __('site.space') }}:</span>
                                <span>{{ $course->space->name }}</span>
                            </div>
                        @endif

                        @if($course->teachers->isNotEmpty())
                            <div class="flex gap-2">
                                <span class="font-medium w-28 shrink-0">{{ __('site.teachers') }}:</span>
                                <span>{{ $course->teachers->map(fn($t) => $t->first_name . ' ' . $t->last_name)->join(', ') }}</span>
                            </div>
                        @endif

                        @if($course->price > 0)
                            <div class="flex gap-2">
                                <span class="font-medium w-28 shrink-0">{{ __('site.course_price') }}:</span>
                                <span>{{ number_format($course->price, 2) }} €</span>
                            </div>
                        @endif

                        @if($course->calendar_notes)
                            <div class="flex gap-2">
                                <span class="font-medium w-28 shrink-0">{{ __('site.course_calendar') }}:</span>
                                <span class="text-xs font-mono">{{ $course->calendar_notes }}</span>
                            </div>
                        @endif

                    </div>
                @endif

                {{-- Peu --}}
                <div class="flex justify-end gap-2 px-6 py-4 border-t dark:border-gray-700">
                    <x-filament::button
                        wire:click="closeCourseModal"
                        color="gray"
                        size="sm"
                    >
                        {{ __('site.cancel') }}
                    </x-filament::button>
                    @if($course?->slug)
                    <x-filament::button
                        tag="a"
                        :href="route('campus.catalog.show', ['slug' => $course->slug, 'preview' => 1])"
                        target="_blank"
                        color="info"
                        size="sm"
                        icon="heroicon-o-eye"
                    >
                        Vista prèvia
                    </x-filament::button>
                    @endif
                    @can('courses.edit')
                    <x-filament::button
                        tag="a"
                        :href="route('filament.admin.resources.courses.edit', $selectedCourseId)"
                        color="primary"
                        size="sm"
                    >
                        {{ __('site.edit') }}
                    </x-filament::button>
                    @endcan
                </div>

            </div>
        </div>
    @endif

    {{-- ── Scripts FullCalendar ────────────────────────────────────────── --}}
    @assets
        @vite('resources/js/calendar.js')
    @endassets

    <script>
        (function () {
            const config = {
                eventsUrl: '{{ route('calendar.events') }}',
                seasonId:  {{ $currentSeasonId ?? 'null' }},
                editable:  @js(auth()->user()?->hasRole('admin')),
            };

            window._calendarConfig = config;

            function boot() {
                if (typeof window.initCalendar === 'function') {
                    window.initCalendar(config);
                } else {
                    // Wait for the module to load
                    document.addEventListener('DOMContentLoaded', () => window.initCalendar(config));
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot);
            } else {
                boot();
            }
        })();
    </script>

</x-filament-panels::page>
