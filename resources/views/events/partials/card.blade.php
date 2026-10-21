<div class="p-4 bg-white dark:bg-gray-800 shadow sm:rounded-lg space-y-2">
    <p class="text-xs font-semibold uppercase text-indigo-600 dark:text-indigo-400">{{ $event->sport->name }}</p>

    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{-- US 6 extra: title links to the public page of the event --}}
        <a href="{{ route('events.public', $event->public_token) }}" class="hover:underline">{{ $event->title }}</a>
    </h3>

    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $event->starts_at->format('d/m/Y H:i') }} · {{ $event->city }}</p>

    <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ trans_choice('{1} :count seat left|[2,*] :count seats left', $event->remainingSeats(), ['count' => $event->remainingSeats()]) }}
    </p>

    <p class="text-sm text-gray-500 dark:text-gray-500">{{ $event->organization->name }}</p>
</div>
