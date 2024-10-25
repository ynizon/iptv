<x-app>
    <div class="col-md-10">
        <div class="row mt-5">
            <div class="col-md-5 mx-auto">
                <a href="/playlists" style="text-align: left">< Retour</a>
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
                        <label for="name">{{__('Url')}}</label>
                        <input type="text" class="form-control" id="url" name="url" placeholder="https://"
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
    </div>
</x-app>
