@if ($event->starts_at->isPast())
    {{-- event already started: no button --}}
@elseif ($event->remainingSeats() <= 0)
    <p class="font-semibold">{{ __('Full') }}</p>
@elseif (auth()->check())
    {{-- registration is not available yet --}}
    <x-primary-button disabled class="opacity-50">{{ __('Participate') }}</x-primary-button>
    <p class="text-sm text-gray-500">{{ __('Registration coming soon.') }}</p>
@else
    <p>{{ __('To participate, please log in or create an account.') }}</p>
    <div class="flex items-center gap-4">
        <a href="{{ route('login') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Log in') }}</a>
        <a href="{{ route('register') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Register') }}</a>
    </div>
@endif
