<x-settings.page :title="__('Playlists')" :subtitle="__('The M3U links of your providers. Their content is imported by the refresh command.')" wide>
    <x-slot name="actions">
        <a href="/playlists/create" class="set-btn set-btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            {{__('New playlist')}}
        </a>
    </x-slot>

    <div class="set-list">
        @forelse ($playlists as $playlist)
            <div class="set-list-row">
                <a href="/playlists/{{$playlist->id}}/edit" class="set-item">
                    <span class="set-item-main">
                        <strong>{{$playlist->name}}</strong>
                        <span>{{__('Last refresh: :date', ['date' => $playlist->updated_at ? $playlist->updated_at->format('d/m/Y H:i') : '—'])}}</span>
                    </span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
            </div>
        @empty
            <div class="set-empty">{{__('No playlist yet.')}}</div>
        @endforelse
    </div>

    <p class="set-note">{{__('To refresh playlists, use')}} <code>php artisan refresh:playlist</code></p>
</x-settings.page>
