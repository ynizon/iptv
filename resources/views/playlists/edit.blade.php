<x-settings.page :title="$playlist->id > 0 ? __('Edit the playlist') : __('New playlist')" back="/playlists" :backLabel="__('Playlists')">
    <form class="set-card" method="post"
          action="{{ $playlist->id > 0 ? route('playlists.update', $playlist->id) : route('playlists.store') }}">
        @if ($playlist->id > 0)
            @method('PUT')
        @endif
        @csrf
        <div class="set-field">
            <label for="name">{{__('Name')}}</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $playlist->name) }}" required autofocus>
        </div>
        <div class="set-field">
            <label for="url">{{__('M3U link')}}</label>
            <input type="url" class="form-control" id="url" name="url" value="{{ old('url', $playlist->url) }}" required
                   placeholder="http://provider.com/get.php?username=XXX&password=YYY&type=m3u_plus&output=ts" aria-describedby="url-help">
            <p class="set-help" id="url-help">{{__('Given by your provider, with your user name and password.')}}</p>
        </div>

        <h2 style="margin: 4px 0 -8px; font-size: 15px; font-weight: 800;">{{__('If your provider is blocked by your DNS')}}</h2>
        <div class="set-row">
            <div class="set-field">
                <label for="tld">{{__('Domain to replace')}}</label>
                <input type="text" class="form-control" id="tld" name="tld" value="{{ old('tld', $playlist->tld) }}" placeholder="provider.com" aria-describedby="tld-help">
                <p class="set-help" id="tld-help">{!! __('Domain name of the links, or <code>*</code> to replace the whole host.') !!}</p>
            </div>
            <div class="set-field">
                <label for="ip">{{__('IP address')}}</label>
                <input type="text" class="form-control" id="ip" name="ip" value="{{ old('ip', $playlist->ip) }}" placeholder="154.70.152.44" aria-describedby="ip-help">
                <p class="set-help" id="ip-help">{{__('Used in place of the domain name.')}}</p>
            </div>
        </div>

        <div class="set-actions">
            <button type="submit" class="set-btn set-btn-primary">{{__('Save')}}</button>
            <a href="/playlists" class="set-btn set-btn-ghost">{{__('Cancel')}}</a>
        </div>
    </form>

    @if ($playlist->id > 0)
        <section class="set-card set-danger" aria-label="{{__('Delete')}}">
            <div class="set-danger-row">
                <p>{{__('Every movie, series and channel of this playlist is removed.')}}</p>
                <form action="{{ route('playlists.destroy', $playlist->id) }}" method="post" onsubmit="return confirm(@js(__('Delete this playlist and all its content?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="set-btn set-btn-danger">{{__('Delete the playlist')}}</button>
                </form>
            </div>
        </section>
    @endif
</x-settings.page>
