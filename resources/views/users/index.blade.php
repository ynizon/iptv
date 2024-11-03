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
                                    <h5 class="">{{__('Users')}}</h5>
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
                            <table class="table text-secondary text-center">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th
                                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                            {{__("Name")}}</th>
                                        <th
                                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                            {{__("Email")}}</th>
                                        <th
                                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                            {{__("Admin")}}</th>
                                        <th
                                            class="text-center text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                            {{__("Action")}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2">
                                            <a href="/users/create" class="list_task_item">
                                                <h6 class="mb-0 text-sm">
                                                    {{__('New user')}}
                                                </h6>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        &nbsp;
                                    </td>
                                    <td>
                                        &nbsp;
                                    </td>
                                    <td>
                                        &nbsp;
                                    </td>
                                </tr>
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="align-middle bg-transparent border-bottom">
                                            {{$user->name}}
                                        </td>
                                        <td class="align-middle bg-transparent border-bottom">{{$user->email}}</td>
                                        <td class="align-middle bg-transparent border-bottom">
                                            <div class="form-check form-switch ps-0" style="display:inline-block">
                                                <input class="form-check-input ms-auto" type="checkbox" value="1"
                                                       name="admin"
                                                       @if ($user->isAdmin() || $user->email == env("ADMIN_EMAIL")) checked @endif>
                                            </div>
                                        </td>

                                        <td class="text-center align-middle bg-transparent border-bottom">
                                            <a href="/users/{{$user->id}}/edit"><i class="fa fa-edit"></i></a>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="nobtn"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                            </form>
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
    </main>
</x-app-layout>

<script src="/assets/js/plugins/datatables.js"></script>

