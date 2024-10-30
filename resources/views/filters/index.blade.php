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
                    <div class="table-responsive">
                        <a href="/settings" style="text-align: left">< {{__("Back")}}</a>
                        <table class="table text-secondary text-center" id="datatable">
                            <thead class="bg-gray-100">
                            <tr>
                                <th class="text-secondary text-xs font-weight-semibold opacity-7">
                                    <h3>{{__('Filters')}}</h3></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex px-2">
                                        <a href="/filters/create" class="list_task_item">
                                            <h6 class="mb-0 text-sm">
                                                {{__('New filter')}}
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
            <x-app.footer />
        </div>
    </main>
</x-app-layout>
