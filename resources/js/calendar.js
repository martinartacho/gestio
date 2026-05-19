import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import caLocale from '@fullcalendar/core/locales/ca';

window.initCalendar = function (config) {
    const el = document.getElementById('campus-calendar');
    if (!el) return;

    if (window._campusCalendar) {
        window._campusCalendar.destroy();
        window._campusCalendar = null;
    }

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale:      caLocale,
        firstDay:    1,
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  '',
        },
        height: 650,
        events: {
            url:         config.eventsUrl,
            method:      'GET',
            extraParams: () => ({ season: window._calendarConfig?.seasonId ?? '' }),
            failure:     () => console.error('Error carregant events del calendari'),
        },
        editable: config.editable ?? false,

        eventClick: function (info) {
            const courseId = info.event.extendedProps?.courseId;
            if (!courseId) return;
            window.Livewire.dispatch('openCourseModal', { courseId });
        },

        eventDrop: function (info) {
            if (!config.editable) { info.revert(); return; }
            const title = info.event.title;
            if (!confirm(`Voleu moure el curs "${title}"?`)) {
                info.revert();
                return;
            }
            window._pendingDrop = info;
            window.Livewire.dispatch('courseDropped', {
                courseId: info.event.extendedProps.courseId,
                newStart: info.event.startStr,
                newEnd:   info.event.endStr ?? '',
            });
            // Clean up after a successful drop (revert path is handled by calendarRevertDrop)
            setTimeout(() => { window._pendingDrop = null; }, 5000);
        },
    });

    calendar.render();
    window._campusCalendar = calendar;
};

window.addEventListener('calendarSeasonChanged', function () {
    window._campusCalendar?.refetchEvents();
});

window.addEventListener('calendarRevertDrop', function () {
    window._pendingDrop?.revert();
    window._pendingDrop = null;
});

document.addEventListener('livewire:navigated', function () {
    if (window._calendarConfig) {
        window.initCalendar(window._calendarConfig);
    }
});
