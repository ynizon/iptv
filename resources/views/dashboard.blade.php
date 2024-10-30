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
                            <input class="form-control border rounded-pill" type="search" placeholder="recherche"
                                   id="search" minlength="3">
                            <input id="category" value="" type="hidden"/>
                        </div>
                    </div>

                    <div id="list">
                    </div>
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>
</x-app-layout>

