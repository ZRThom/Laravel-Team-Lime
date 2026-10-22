<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}@if ($organization) : {{ $organization->name }}@endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (! $organization)
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-gray-700 dark:text-gray-300">
                    {{ __('No organization yet.') }}
                    <a href="{{ route('organizations.create') }}" class="underline">{{ __('Create one') }}</a>
                </div>
            @elseif (! $canManage)
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-gray-700 dark:text-gray-300">
                    {{ __('Only owners can see the events of an organization.') }}
                </div>
            @else
                @foreach ([__('Upcoming events') => $upcoming, __('Finished events') => $finished, __('Cancelled events') => $cancelled] as $title => $events)
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $title }}</h3>

                        @if ($events->isEmpty())
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('No event.') }}</p>
                        @else
                            <table class="mt-4 w-full text-left text-sm text-gray-700 dark:text-gray-300">
                                <thead>
                                    <tr>
                                        <th class="py-2">{{ __('Title') }}</th>
                                        <th class="py-2">{{ __('Sport') }}</th>
                                        <th class="py-2">{{ __('Date') }}</th>
                                        <th class="py-2">{{ __('City') }}</th>
                                        <th class="py-2">{{ __('Status') }}</th>
                                        <th class="py-2">{{ __('Registered') }}</th>
                                        <th class="py-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($events as $event)
                                        <tr>
                                            <td class="py-2">{{ $event->title }}</td>
                                            <td class="py-2">{{ $event->sport->name }}</td>
                                            <td class="py-2">{{ $event->starts_at->format('d/m/Y H:i') }}</td>
                                            <td class="py-2">{{ $event->city }}</td>
                                            <td class="py-2">{{ $event->status }}</td>
                                            <td class="py-2">{{ $event->confirmed_count }} / {{ $event->capacity }}</td>
                                            <td class="py-2">
                                                <a href="{{ route('events.manage', $event) }}" class="underline">{{ __('Manage') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
