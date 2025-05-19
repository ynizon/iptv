<x-app-layout>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <div class="container-fluid py-4">
            <div class="mt-4 row">
                <div class="col-12">
                    <a href="/settings" style="text-align: left">< {{__("Back")}}</a>
                    <div class="card">
                        <div class="pb-0 card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="">{{__('Categories')}}</h5>
                                    <p class="mb-0 text-sm">

                                    </p>
                                </div>
                                <div class="col-6 text-end">
                                </div>
                            </div>
                        </div>
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
                            <div class="table-responsive">
                                <table class="table text-secondary text-center" id="datatable">
                                    <thead class="bg-gray-100">
                                    <tr>
                                        <th
                                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                            {{__("From")}} -> {{__("To")}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2">
                                                <a href="/categories/create" class="list_task_item">
                                                    <h6 class="mb-0 text-sm">
                                                        {{__('New category')}}
                                                    </h6>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2">
                                                    <a href="/categories/{{$category->id}}/edit" class="list_task_item">
                                                        <h6 class="mb-0 text-sm">
                                                            {{$category->from}} -> {{$category->to}}
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
            </div>
            <x-app.footer />
        </div>
    </main>
</x-app-layout>
