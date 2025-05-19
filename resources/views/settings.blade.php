<x-app-layout>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-md-12 px-4">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert" id="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert" id="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="row">

            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="col-md-5 mx-auto">
                        <div class="row">
                            <a href="/dashboard" style="text-align: left">< {{__("Back")}}</a>
                            <br/>
                            <a class="tl" href="/playlists">{{__("Playlists")}}</a>
                            <br/>
                            <a class="tl" href="/filters">{{__("Filters")}}</a>
                            <br/>
                            <a class="tl" href="/categories">{{__("Categories")}}</a>

                            @if (Auth::user()->isAdmin())
                                <br/>
                                <a class="tl" href="/users">{{__("Users")}}</a>
                            @endif
                        </div>
                        <hr/>
                        <div class="row">
                            <form method="post" action="/settings">
                                {{__('Show only formats')}}:
                                @csrf
                                <div class="">
                                    @foreach (\App\Http\Controllers\SearchController::FILTERS as $format)
                                        <div class="form-check px-5">
                                            <input class="form-check-input formats"
                                                   @if (in_array($format, json_decode(Auth::user()->formats)))
                                                    checked
                                                   @endif
                                            name="formats[]" type="checkbox" id="chk{{$format}}" value="{{$format}}">
                                            <label class="form-check-label" for="chk{{$format}}">{{$format}}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <br/>
                                <input type="submit" class="btn btn-primary" />

                            </form>
                        </div>
                        <hr/>
                        Windows:
                        <ul>
                            <li>Install <a class="di" href="https://www.videolan.org/vlc/index.fr.html">VLC</a></li>
                            <li>Launch this file  <a class="di" href="/download/iptvreg">iptv.reg</a></li>
                            <li>Create a folder c:\IPTV with this file inside: <a class="di" href="/iptv/iptv_vlc.bat">iptv_vlc.bat</a></li>
                        </ul>
                        <hr/>
                        Linux:
                        <ul>
                            <li>
                                Copy this file <a class="di" href="/download/iptvdesktop">iptv.desktop</a> into ~/.local/share/applications/
                            </li>
                            <li>
                                Copy this file <a class="di" href="/download/iptvsh">iptv_vlc.sh</a> into /usr/local/bin
                            </li>
                            <li>
                                In your terminal launch: xdg-mime default iptv.desktop x-scheme-handler/iptv_vlc.sh
                            </li>
                        </ul>
                        <hr/>
                        Note:<br/>
                        If you can't access to your iptv server, try another dns or update your host file
                    </div>
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>
</x-app-layout>

