<x-guest-layout>
    <main class="main-content  mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="position-absolute w-40 top-0 start-0 h-100 d-md-block d-none">
                                <div class="oblique-image position-absolute d-flex fixed-top ms-auto h-100 z-index-0 bg-cover me-n8"
                                     style="background-image:url('/assets/img/image-sign-up.jpg')">
                                    <div class="my-auto text-start max-width-350 ms-7">
                                        <h1 class="mt-3 text-white font-weight-bolder">{{__("Screenshots")}}</h1>
                                        <p class="text-white text-lg mt-4 mb-4"> </p>

                                        <div class="d-flex align-items-center">
                                            <div class="avatar-group d-flex">
                                                <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                                    <div class="carousel-indicators">
                                                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                                    </div>
                                                    <div class="carousel-inner">

                                                        @foreach ($pictures as $picture)
                                                            <div class="carousel-item {{$loop->index == 0 ? 'active' : ''}}">
                                                                <img src="/screenshots/{{basename($picture)}}" class="d-block w-100" alt="screenshot">
                                                            </div>
                                                        @endforeach

                                                    </div>
                                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">{{__("Previous")}}</span>
                                                    </button>
                                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">{{__("Next")}}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-start position-absolute fixed-bottom ms-7">
                                        <h6 class="text-white text-sm mb-5"></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex flex-column mx-auto">
                            <div class="card card-plain mt-8-old">
                                <div class="card-header pb-0 text-left bg-transparent">
                                    <h3 class="font-weight-black text-dark display-6">{{config("app.name")}}</h3>
                                    <br/>
                                    <p class="mb-0">Web Interface to have your IPTV Server everywhere.
                                        <br/><br/>
                                        Features:<br/>
                                        <ul>
                                            <li> use VLC for viewing the stream</li>
                                            <li> added multiple playlists</li>
                                            <li> set your favorites movies, channels, series</li>
                                            <li> save the history by user</li>
                                            <li> memorize your position</li>
                                            <li> filter some categories to avoid this kind of movies in search.</li>
                                        </ul>
                                    </p>
                                    <br/>

                                    <a class="btn btn-primary text-white" href="{{route('login')}}")>{{__("Login")}}</a>
                                    <div class="card-body">
                                        <i class="fa fa-github pad"></i><a href="https://github.com/ynizon/iptv">https://github.com/ynizon/iptv</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

</x-guest-layout>
