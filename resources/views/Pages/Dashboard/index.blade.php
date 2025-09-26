{{-- @extends('Layout.layout')

@section('pages', 'Dashboard')

@section('title', config('app.name') . ' | Dashboard')

@section('content')

    <div class="card">
        <div class="card-body">
            <div id='calendar'></div>
        </div>

    </div>


@endsection

@push('js')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth'
            });
            calendar.render();
        });
    </script>
@endpush --}}

@extends('Layout.layout')

@section('pages', 'Dashboard')
@section('title', config('app.name') . ' | Dashboard')

@push('css')
    <style>
        /* Untuk FullCalendar versi dayGrid / month view */
        .fc-day-grid-event .fc-event-title,
        .fc-daygrid-event .fc-event-title {
            white-space: normal !important;
            /* bukan nowrap */
            word-wrap: break-word;
            /* pecah kata jika terlalu panjang */
        }

        /* Kadang bagian kontainernya perlu juga: */
        .fc-event-main-frame,
        .fc-event-title-container {
            white-space: normal !important;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <h1>Agenda Unit Kemahasiswaan ITATS</h1>
                </div>


            </div>
            <div id="calendar"></div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/locales-all.global.min.js'></script>
    <script>
        (function() {

            let dataSet = @json($data ?? []);

            $(document).ready(function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {

                    initialView: 'dayGridMonth',
                    timeZone: 'Asia/Jakarta',
                    locale: 'id',

                    displayEventTime: false,
                    displayEventEnd: false,
                    dayMaxEvents: 3,
                    aspectRatio: 2,

                    headerToolbar: {
                        left: '',
                        center: 'title'
                    },
                    footerToolbar: {
                        right: 'today prev,next'
                    },

                    // eventTimeFormat: {
                    //     hour: '2-digit',
                    //     minute: '2-digit',
                    //     hour12: false,
                    // },

                    events: dataSet,

                    nextDayThreshold: '00:00:00'

                });

                calendar.render();

            });
        })()
    </script>
@endpush
