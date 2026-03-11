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
                <div class="col-lg-12 col-md-12">
                   <div class="row">
                        <div class="col-md-5 mx-auto">
                            <form method="post" action="/m3u">
                                @csrf
                                <input class="form-control border rounded-pill" type="search" placeholder="recherche"
                                       name="search" id="search_m3u" minlength="2">
                                <input id="category" value="" type="hidden"/>
                            </form>
                        </div>
                    </div>

                    <div id="list_m3u" class="col-md-5 mx-auto">
                        <form method="post">
                            <textarea name="content" rows="20" cols="120">{{$content}}</textarea>
                            @csrf
                            <ul>
                                @foreach ($urls["movies"] as $url)
                                    <li>
                                        <label for="url_{{$url['id']}}"><input id="url_{{$url['id']}}" type="checkbox" name="urlsOK[]" value="{{$url['id']}}">&nbsp;&nbsp;&nbsp;{{$url['name']}}</label>
                                        <a style="display:inline" href="iptv://{{$url->urlFinal}}">VIDEO</a>
                                    </li>
                                @endforeach
                                @foreach ($urls["series"] as $serie => $seasons)
                                        <li>
                                            <label for="url_{{$url['id']}}"><input id="url_{{$url['id']}}" type="checkbox" name="urlsOK[]" value="{{$url['id']}}">&nbsp;&nbsp;&nbsp;{{$url['name']}}</label>
                                            <a style="display:inline" href="iptv://{{$url->urlFinal}}">VIDEO</a>
                                        </li>
                                @endforeach
                                @foreach ($urls["channels"] as $url)
                                        <li>
                                            <label for="url_{{$url['id']}}"><input id="url_{{$url['id']}}" type="checkbox" name="urlsOK[]" value="{{$url['id']}}">&nbsp;&nbsp;&nbsp;{{$url['name']}}</label>
                                            <a style="display:inline" href="iptv://{{$url->urlFinal}}">VIDEO</a>
                                        </li>
                                @endforeach
                            </ul>
                            <input type="submit" value="Ajouter" />
                        </form>
                    </div>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                   $("#recent").click();
                });
            </script>
            <x-app.footer />
        </div>
    </main>
</x-app-layout>

