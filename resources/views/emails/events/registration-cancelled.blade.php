<p>{{ __('Hello :name,', ['name' => $participant->name]) }}</p>

<p>{{ __('Your registration to :title is cancelled.', ['title' => $event->title]) }}</p>

<ul>
    <li>{{ $event->starts_at->format('d/m/Y H:i') }}</li>
    <li>{{ $event->venue }}, {{ $event->city }}</li>
</ul>

<p><a href="{{ route('events.public', $event->public_token) }}">{{ __('See the event') }}</a></p>
