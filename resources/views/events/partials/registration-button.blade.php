@if ($event->starts_at->isPast())
    {{-- event already started: no button --}}
@elseif (auth()->check() && $event->isRegisteredBy(auth()->user()))
    <form method="post" action="{{ route('events.registration.destroy', $event) }}">
        @csrf
        @method('delete')
        <x-danger-button>{{ __('Cancel my registration') }}</x-danger-button>
    </form>
@elseif ($event->remainingSeats() <= 0)
    <p class="font-semibold">{{ __('Full') }}</p>
@elseif (auth()->check())
    <form method="post" action="{{ route('events.registration.store', $event) }}">
        @csrf
        <x-primary-button>{{ __('Participate') }}</x-primary-button>
    </form>
@else
    <p>{{ __('To participate, please log in or create an account.') }}</p>
    <div class="flex items-center gap-4">
        <a href="{{ route('login') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Log in') }}</a>
        <a href="{{ route('register') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Register') }}</a>
    </div>
@endif
