<p>{{ __('Hello :name,', ['name' => $participant->name]) }}</p>

<p>{{ __('Your seat is booked for the event below.') }}</p>

<ul>
    <li>{{ $event->title }} ({{ $event->sport->name }})</li>
    <li>{{ $event->starts_at->format('d/m/Y H:i') }}, {{ $event->duration_minutes }} min</li>
    <li>{{ $event->venue }}, {{ $event->city }}</li>
    <li>{{ __('Organized by') }} {{ $event->organization->name }}</li>
</ul>

<p><a href="{{ route('events.public', $event->public_token) }}">{{ __('See the event') }}</a></p>
