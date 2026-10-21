<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl space-y-4 text-gray-700 dark:text-gray-300">
                    <p class="text-xs font-semibold uppercase text-indigo-600 dark:text-indigo-400">{{ $event->sport->name }}</p>

                    <p>{{ __('Organized by') }} <strong>{{ $event->organization->name }}</strong></p>

                    <p>{{ __('Date and time') }} : {{ $event->starts_at->format('d/m/Y H:i') }}</p>
                    <p>{{ __('Duration') }} : {{ $event->duration_minutes }} min</p>
                    <p>{{ __('City') }} : {{ $event->city }}</p>
                    <p>{{ __('Venue') }} : {{ $event->venue }}</p>

                    @if ($event->description)
                        <p>{{ $event->description }}</p>
                    @endif

                    <p>{{ __('Capacity') }} : {{ $event->capacity }}</p>
                    <p>{{ __('Seats left') }} : {{ $event->remainingSeats() }}</p>

                    @include('events.partials.registration-button')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
