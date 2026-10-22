<p>{{ __(':name just registered to your event :title.', ['name' => $participant->name, 'title' => $event->title]) }}</p>

<ul>
    <li>{{ __('Participant') }} : {{ $participant->name }} ({{ $participant->email }})</li>
    <li>{{ __('Date and time') }} : {{ $event->starts_at->format('d/m/Y H:i') }}</li>
    <li>{{ __('Seats left') }} : {{ $event->remainingSeats() }} / {{ $event->capacity }}</li>
</ul>

<p><a href="{{ route('events.participants', $event) }}">{{ __('See the participants') }}</a></p>
