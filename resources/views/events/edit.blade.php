<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit event') }} : {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Event Information') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $event->organization->name }} · {{ __('Status') }} : {{ $event->status }}
                            </p>

                            @if (session('status'))
                                <p class="mt-2 text-sm text-green-600 dark:text-green-400">{{ session('status') }}</p>
                            @endif
                        </header>

                        <form method="post" action="{{ route('events.update', $event) }}" class="mt-6 space-y-6">
                            @csrf
                            @method('put')

                            <div>
                                <x-input-label for="sport_id" :value="__('Sport')" />
                                <select id="sport_id" name="sport_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    @foreach ($sports as $sport)
                                        <option value="{{ $sport->id }}" @selected((int) old('sport_id', $event->sport_id) === $sport->id)>{{ $sport->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('sport_id')" />
                            </div>

                            <div>
                                <x-input-label for="title" :value="__('Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $event->title)" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('title')" />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $event->description) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                            </div>

                            <div>
                                <x-input-label for="starts_at" :value="__('Date and time')" />
                                <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full" :value="old('starts_at', $event->starts_at->format('Y-m-d\TH:i'))" required />
                                <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                            </div>

                            <div>
                                <x-input-label for="duration_minutes" :value="__('Duration (minutes, 30 to 240)')" />
                                <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="30" max="240" class="mt-1 block w-full" :value="old('duration_minutes', $event->duration_minutes)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('duration_minutes')" />
                            </div>

                            <div>
                                <x-input-label for="city" :value="__('City')" />
                                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $event->city)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('city')" />
                            </div>

                            <div>
                                <x-input-label for="venue" :value="__('Venue')" />
                                <x-text-input id="venue" name="venue" type="text" class="mt-1 block w-full" :value="old('venue', $event->venue)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('venue')" />
                            </div>

                            <div>
                                <x-input-label for="capacity" :value="__('Capacity (2 to 100 participants)')" />
                                <x-text-input id="capacity" name="capacity" type="number" min="2" max="100" class="mt-1 block w-full" :value="old('capacity', $event->capacity)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('capacity')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            @if ($event->status !== 'cancelled')
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section class="space-y-6">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Cancel event') }}
                                </h2>

                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('Registrations are kept for the history.') }}
                                </p>
                            </header>

                            <form method="post" action="{{ route('events.cancel', $event) }}">
                                @csrf
                                @method('patch')

                                <x-danger-button onclick="return confirm('{{ __('Cancel this event?') }}')">
                                    {{ __('Cancel event') }}
                                </x-danger-button>
                            </form>
                        </section>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
