<x-app>
    <div class="col-md-10">
        <div class="row mt-5">
            <div class="col-md-5 mx-auto">
                <a href="/filters" style="text-align: left">< Retour</a>
                <form action="@if ($filter->id > 0) {{ route('filters.update', $filter->id) }} @else {{ route('filters.store') }} @endif"
                      method="post" >
                    @if ($filter->id > 0)
                        @method('PUT')
                    @endif
                    @csrf
                    <div class="form-group">
                        <label for="name">{{__('Name')}}</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{$filter->name}}" required>
                    </div>

                    <br>
                    <button type="submit" class="btn btn-primary">
                        <i class="pad fas fa-save" aria-hidden="true"></i> {{__("Save")}}
                    </button>
                </form>

                @if ($filter->id > 0)
                    <form action="{{ route('filters.destroy', $filter->id) }}" method="post">
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
