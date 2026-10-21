<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Upcoming events') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                @include('events.partials.filters')
            </div>

            @if ($events->isEmpty())
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-center">
                    <p class="text-gray-700 dark:text-gray-300">{{ __('No event matches your search.') }}</p>

                    <a href="{{ route('events.index') }}" class="mt-2 inline-block underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                        {{ __('Clear filters') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 px-4 sm:px-0">
                    @foreach ($events as $event)
                        @include('events.partials.card')
                    @endforeach
                </div>

                <div class="px-4 sm:px-0">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
