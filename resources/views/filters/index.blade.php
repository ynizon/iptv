<x-app>
    <div class="col-md-10">
        <div class="row mt-5">
            <div class="col-md-5 mx-auto">
                <div class="row justify-content-center">
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

                <div class="table-responsive">
                    <a href="/" style="text-align: left">< Retour</a>
                    <table class="table text-secondary text-center" id="datatable">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="text-secondary text-xs font-weight-semibold opacity-7">
                                {{__('Filter')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex px-2">
                                        <a href="/filters/create" class="list_task_item">
                                            <h6 class="mb-0 text-sm">
                                                Nouveau filtre
                                            </h6>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @foreach($filters as $filter)
                            <tr>
                                <td>
                                    <div class="d-flex px-2">
                                        <a href="/filters/{{$filter->id}}/edit" class="list_task_item">
                                            <h6 class="mb-0 text-sm">
                                                {{$filter->name}}
                                            </h6>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app>
