<form method="get" action="{{ route('events.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
    <div>
        <x-input-label for="sport_id" :value="__('Sport')" />
        <select id="sport_id" name="sport_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            <option value="">{{ __('All sports') }}</option>
            @foreach ($sports as $sport)
                <option value="{{ $sport->id }}" @selected((int) ($filters['sport_id'] ?? 0) === $sport->id)>{{ $sport->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="city" :value="__('City')" />
        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="$filters['city'] ?? ''" />
    </div>

    <div>
        <x-input-label for="date" :value="__('Date')" />
        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="$filters['date'] ?? ''" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Filter') }}</x-primary-button>

        <a href="{{ route('events.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
            {{ __('Clear filters') }}
        </a>
    </div>
</form>
