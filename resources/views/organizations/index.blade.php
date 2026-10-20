<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Organizations') }}
            </h2>

            <a href="{{ route('organizations.create') }}">
                <x-primary-button type="button">{{ __('New organization') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-gray-800 shadow sm:rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($organizations->isEmpty())
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('No organizations yet.') }}
                        </p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    <th class="px-4 py-3">{{ __('Name') }}</th>
                                    <th class="px-4 py-3">{{ __('Slug') }}</th>
                                    <th class="px-4 py-3">{{ __('Plan') }}</th>
                                    <th class="px-4 py-3">{{ __('Members') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($organizations as $organization)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium">{{ $organization->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $organization->slug }}</td>
                                        <td class="px-4 py-3 text-sm capitalize">{{ $organization->plan }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $organization->users()->count() }}</td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            @can('update', $organization)
                                                <a href="{{ route('organizations.edit', $organization) }}" class="underline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                                    {{ __('Edit') }}
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
