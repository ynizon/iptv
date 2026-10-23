@php($userFormats = json_decode(Auth::user()->formats) ?? [])
<x-settings.page :title="__('Settings')" back="/dashboard" :backLabel="__('Catalog')" wide>
    <nav class="set-nav" aria-label="{{__('Settings')}}">
        <a class="set-nav-item" href="/playlists">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h10M4 18h7M17 14v6M14 17h6"/></svg>
            <strong>{{__('Playlists')}}</strong>
            <span>{{__('The M3U links of your providers.')}}</span>
        </a>
        <a class="set-nav-item" href="/visible-categories">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            <strong>{{__('Visible categories')}}</strong>
            <span>{{__('Choose the categories shown in the menu.')}}</span>
        </a>
        <a class="set-nav-item" href="/filters">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18l-7 8v6l-4 2v-8z"/></svg>
            <strong>{{__('Filters')}}</strong>
            <span>{{__('Hide the categories containing a word.')}}</span>
        </a>
        <a class="set-nav-item" href="/categories">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h11M4 12h7M4 17h11M17 9l3 3-3 3"/></svg>
            <strong>{{__('Categories')}}</strong>
            <span>{{__('Rename and group the categories.')}}</span>
        </a>
        @if (Auth::user()->isAdmin())
            <a class="set-nav-item" href="/users">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6M17 4a4 4 0 0 1 0 8M22 21c0-3-1.5-5-4-5.6"/></svg>
                <strong>{{__('Users')}}</strong>
                <span>{{__('Accounts and roles.')}}</span>
            </a>
        @endif
        <a class="set-nav-item" href="/profile">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-6 8-6s8 2 8 6"/></svg>
            <strong>{{__('My account')}}</strong>
            <span>{{__('Name, email and password.')}}</span>
        </a>
    </nav>

    <form class="set-card" method="post" action="/settings">
        @csrf
        <h2>{{__('Video formats')}}</h2>
        <p>{{__('Only the checked qualities are shown in the catalog.')}}</p>
        <div class="set-checks">
            @foreach (\App\Http\Controllers\SearchController::FILTERS as $format)
                <label class="set-check" for="chk{{$format}}">
                    <input class="formats" type="checkbox" name="formats[]" id="chk{{$format}}" value="{{$format}}" @checked(in_array($format, $userFormats))>
                    {{$format}}
                </label>
            @endforeach
        </div>
        <div class="set-actions">
            <button type="submit" class="set-btn set-btn-primary">{{__('Save')}}</button>
        </div>
    </form>

    <section class="set-card">
        <h2>{{__('Play with VLC')}}</h2>
        <p>{{__('The Play buttons open the video in VLC: install it once on each computer.')}}</p>
        <div class="set-row">
            <div class="set-field">
                <strong>Windows</strong>
                <ol class="set-steps">
                    <li>{{__('Install')}} <a href="https://www.videolan.org/vlc/index.fr.html">VLC</a></li>
                    <li>{{__('Open this file:')}} <a href="/download/iptvreg">iptv.reg</a></li>
                    <li>{!! __('Put this file in a <code>C:\IPTV</code> folder:') !!} <a href="/iptv/iptv_vlc.bat">iptv_vlc.bat</a></li>
                </ol>
            </div>
            <div class="set-field">
                <strong>Linux</strong>
                <ol class="set-steps">
                    <li>{!! __('Copy :file to <code>~/.local/share/applications/</code>', ['file' => '<a href="/download/iptvdesktop">iptv.desktop</a>']) !!}</li>
                    <li>{!! __('Copy :file to <code>/usr/local/bin</code>', ['file' => '<a href="/download/iptvsh">iptv_vlc.sh</a>']) !!}</li>
                    <li>{{__('Run:')}} <code>xdg-mime default iptv.desktop x-scheme-handler/iptv_vlc.sh</code></li>
                </ol>
            </div>
        </div>
        <p class="set-help">{{__('If you cannot reach your IPTV server, try another DNS or update your hosts file.')}}</p>
    </section>
</x-settings.page>
