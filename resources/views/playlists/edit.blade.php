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
                <div class="col-lg-8 col-md-8">
                    <a href="/settings" style="text-align: left">< {{__("Back")}}</a>
                    <form action="@if ($playlist->id > 0) {{ route('playlists.update', $playlist->id) }} @else {{ route('playlists.store') }} @endif"
                          method="post" >
                        @if ($playlist->id > 0)
                            @method('PUT')
                        @endif
                        @csrf
                        <div class="form-group">
                            <label for="name">{{__('Name')}}</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="{{$playlist->name}}" required>
                        </div>

                        <div class="form-group">
                            <label for="url">{{__('Url')}}</label>
                            <input type="text" class="form-control" id="url" name="url" placeholder="http://yourwebsite/get.php?username=XXX&password=YYY&type=m3u_plus&output=ts.m3U"
                                   value="{{$playlist->url}}" required>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary">
                            <i class="pad fas fa-save" aria-hidden="true"></i> {{__("Save")}}
                        </button>
                    </form>

                    @if ($playlist->id > 0)
                        <form action="{{ route('playlists.destroy', $playlist->id) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger float-end"><i class="pad fas fa-trash" aria-hidden="true"></i>
                                {{__("Delete")}}</button>
                        </form>
                    @endif
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>
</x-app-layout>
