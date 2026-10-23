<x-settings.page :title="__('Users')" :subtitle="__('The people who can log in, and their short link to send a stream to the TV.')" wide>
    <x-slot name="actions">
        <a href="/users/create" class="set-btn set-btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            {{__('New user')}}
        </a>
    </x-slot>

    <div class="set-list">
        @foreach ($users as $user)
            <div class="set-list-row">
                <a href="/users/{{$user->id}}/edit" class="set-item">
                    <span class="set-item-main">
                        <strong>
                            {{$user->name}}
                            @if ($user->isAdmin() || $user->email == env("ADMIN_EMAIL"))
                                <span class="set-badge">{{__('Admin')}}</span>
                            @endif
                        </strong>
                        <span>{{$user->email}} · {{__('TV link')}} /tv/{{$user->tvToken()}}</span>
                    </span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
                <form action="{{ route('users.destroy', $user->id) }}" method="post" onsubmit="return confirm(@js(__('Delete :name?', ['name' => $user->name])))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="set-icon-btn" aria-label="{{__('Delete :name', ['name' => $user->name])}}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/></svg>
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</x-settings.page>
