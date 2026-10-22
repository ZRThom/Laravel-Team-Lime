<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage event') }} : {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl space-y-2 text-gray-700 dark:text-gray-300">
                    <p>{{ $event->organization->name }} · {{ $event->sport->name }}</p>
                    <p>{{ __('Date and time') }} : {{ $event->starts_at->format('d/m/Y H:i') }}</p>
                    <p>{{ __('City') }} : {{ $event->city }}</p>
                    <p>{{ __('Venue') }} : {{ $event->venue }}</p>
                    <p>{{ __('Status') }} : {{ $event->status }}</p>
                    <p>{{ __('Registered') }} : {{ $event->confirmed_count }} / {{ $event->capacity }}</p>

                    <div class="flex items-center gap-4 pt-2">
                        <a href="{{ route('events.participants', $event) }}" class="underline">{{ __('Participants') }}</a>
                        <a href="{{ route('events.edit', $event) }}" class="underline">{{ __('Edit') }}</a>
                        <a href="{{ route('dashboard') }}" class="underline">{{ __('Back to the dashboard') }}</a>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Public link') }}</h3>

                    <div class="mt-2 flex items-center gap-2">
                        <x-text-input id="public-link" type="text" readonly class="block w-full" :value="route('events.public', $event->public_token)" />
                        <x-secondary-button onclick="navigator.clipboard.writeText(document.getElementById('public-link').value)">
                            {{ __('Copy') }}
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
