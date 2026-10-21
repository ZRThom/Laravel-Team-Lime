<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Event Information') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Create a new event. It is published right away.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('events.store') }}" class="mt-6 space-y-6">
                            @csrf

                            <div>
                                <x-input-label for="organization_id" :value="__('Organization')" />
                                <select id="organization_id" name="organization_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    @foreach ($organizations as $organization)
                                        <option value="{{ $organization->id }}" @selected((int) old('organization_id', session('organization_id')) === $organization->id)>{{ $organization->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('organization_id')" />
                            </div>

                            <div>
                                <x-input-label for="sport_id" :value="__('Sport')" />
                                <select id="sport_id" name="sport_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    @foreach ($sports as $sport)
                                        <option value="{{ $sport->id }}" @selected((int) old('sport_id') === $sport->id)>{{ $sport->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('sport_id')" />
                            </div>

                            <div>
                                <x-input-label for="title" :value="__('Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('title')" />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                            </div>

                            <div>
                                <x-input-label for="starts_at" :value="__('Date and time')" />
                                <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full" :value="old('starts_at')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                            </div>

                            <div>
                                <x-input-label for="duration_minutes" :value="__('Duration (minutes, 30 to 240)')" />
                                <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="30" max="240" class="mt-1 block w-full" :value="old('duration_minutes')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('duration_minutes')" />
                            </div>

                            <div>
                                <x-input-label for="city" :value="__('City')" />
                                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('city')" />
                            </div>

                            <div>
                                <x-input-label for="venue" :value="__('Venue')" />
                                <x-text-input id="venue" name="venue" type="text" class="mt-1 block w-full" :value="old('venue')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('venue')" />
                            </div>

                            <div>
                                <x-input-label for="capacity" :value="__('Capacity (2 to 100 participants)')" />
                                <x-text-input id="capacity" name="capacity" type="number" min="2" max="100" class="mt-1 block w-full" :value="old('capacity')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('capacity')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Create') }}</x-primary-button>

                                <a href="{{ route('dashboard') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
