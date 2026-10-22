<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Participants') }} : {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $participants->count() }} / {{ $event->capacity }} {{ __('confirmed participants') }}
                </p>

                @if ($participants->isEmpty())
                    <p class="mt-4 text-gray-700 dark:text-gray-300">{{ __('No participant yet.') }}</p>
                @else
                    <table class="mt-4 w-full text-left text-sm text-gray-700 dark:text-gray-300">
                        <thead>
                            <tr>
                                <th class="py-2">{{ __('Name') }}</th>
                                <th class="py-2">{{ __('Email') }}</th>
                                <th class="py-2">{{ __('Registered on') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($participants as $participant)
                                <tr>
                                    <td class="py-2">{{ $participant->name }}</td>
                                    <td class="py-2">{{ $participant->email }}</td>
                                    <td class="py-2">{{ \Illuminate\Support\Carbon::parse($participant->pivot->registered_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <a href="{{ route('events.manage', $event) }}" class="mt-6 inline-block underline text-sm text-gray-600 dark:text-gray-400">{{ __('Back to the event') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
