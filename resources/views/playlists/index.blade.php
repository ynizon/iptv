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
                                    <h5 class="">{{__('Playlists')}}</h5>
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
                                            {{__("Name")}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2">
                                                <a href="/playlists/create" class="list_task_item">
                                                    <h6 class="mb-0 text-sm">
                                                        {{__('New playlist')}}
                                                    </h6>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach($playlists as $playlist)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2">
                                                    <a href="/playlists/{{$playlist->id}}/edit" class="list_task_item">
                                                        <h6 class="mb-0 text-sm">
                                                            {{$playlist->name}} ({{$playlist->updated_at}})
                                                        </h6>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <p style="text-align: center">
                                    {{__("To refresh playlists, use")}} :<br/><bold>php artisan refresh:playlist</bold>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>
</x-app-layout>
