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

{{-- Exportació WooCommerce --}}
        <div class="ml-auto">
            <x-filament::button
                tag="a"
                :href="route('calendar.export.woocommerce', ['season' => $currentSeasonId])"
                color="gray"
                size="sm"
                icon="heroicon-o-arrow-down-tray"
            >
                {{ __('site.export_wp') }}
            </x-filament::button>
        </div>

    </div>

    {{-- ── Contenidor FullCalendar ─────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 overflow-x-auto">
        <div id="campus-calendar"></div>
    </div>

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
