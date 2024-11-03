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
                    <form action="@if ($user->id > 0) {{ route('users.update', $user->id) }} @else {{ route('users.store') }} @endif"
                          method="post" >
                        @if ($user->id > 0)
                            @method('PUT')
                        @endif
                        @csrf
                        <div class="form-group">
                            <label for="name">{{__('Name')}}</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="{{$user->name}}" required>
                        </div>
                        @if ($user->id == 0)
                            <div class="form-group">
                                <label for="password">{{__('Password')}}</label>
                                <input type="password" class="form-control" id="password" name="password"
                                       value="" required>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="url">{{__('Email')}}</label>
                            <input type="email" class="form-control" id="url" name="email"
                                   value="{{$user->email}}" required>
                        </div>

                        <div class="form-group">
                            <label for="url">{{__('Role')}}</label>
                            <select name="admin" class="form-control">
                                <option value="0">{{__("User")}}</option>
                                <option value="1">{{__("Admin")}}</option>
                            </select>
                        </div>

                        <br>
                        <button type="submit" class="btn btn-primary">
                            <i class="pad fas fa-save" aria-hidden="true"></i> {{__("Save")}}
                        </button>
                    </form>
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>
</x-app-layout>
