<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Members') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Manage the users who belong to this organization.') }}
        </p>
    </header>

    <form method="post" action="{{ route('organizations.members.store', $organization) }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="user_id" :value="__('User ID')" />
            <x-text-input id="user_id" name="user_id" type="number" class="mt-1 block w-full" :value="old('user_id')" required />
            <x-input-error class="mt-2" :messages="$errors->get('user_id')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Add member') }}</x-primary-button>

            @if (session('status') === 'member-added')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Member added.') }}</p>
            @endif
        </div>
    </form>

    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse ($organization->users as $member)
            <li class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $member->name }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $member->email }} · <span class="capitalize">{{ $member->pivot->role }}</span>
                    </p>
                </div>

                @if ($member->pivot->role !== 'owner')
                    <form method="post" action="{{ route('organizations.members.destroy', [$organization, $member]) }}">
                        @csrf
                        @method('delete')

                        <x-danger-button>{{ __('Remove') }}</x-danger-button>
                    </form>
                @endif
            </li>
        @empty
            <li class="py-3 text-sm text-gray-600 dark:text-gray-400">
                {{ __('No members yet.') }}
            </li>
        @endforelse
    </ul>
</section>
