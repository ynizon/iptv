<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title itemprop="name">
        {{config("app.name")}}
    </title>
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@enpix" />
    <meta name="twitter:creator" content="@enpix" />
    <meta name="twitter:title" content="{{config("app.name")}}" />
    <meta name="twitter:description" content="IPTView" />
    <meta name="twitter:image"
        content="https://s3.amazonaws.com/creativetim_bucket/products/737/original/corporate-ui-dashboard-laravel.jpg?1695288974" />
    <meta name="twitter:url" content="{{config("app.url")}}" />
    <meta name="description" content="IPTView">
    <meta name="keywords" content="">
    <meta property="og:app_id" content="">
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{config("app.name")}}">
    <meta property="og:url" content="{{config("app.url")}}">
    <meta property="og:image"
        content="https://s3.amazonaws.com/creativetim_bucket/products/737/original/corporate-ui-dashboard-laravel.jpg?1695288974">
    <meta property="product:price:amount" content="FREE">
    <meta property="product:price:currency" content="EUR">
    <meta property="product:availability" content="in Stock">
    <meta property="product:brand" content="">
    <meta property="product:category" content="Admin &amp; Dashboards">
    <meta name="data-turbolinks-track" content="false">

    <link rel="apple-touch-icon" sizes="76x76" href="/assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <title>
        {{config("app.name")}}
    </title>
    <!--     Fonts and icons     -->
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Noto+Sans:300,400,500,600,700,800|PT+Mono:300,400,500,600,700"
        rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="/assets/css/nucleo-svg.css" rel="stylesheet" />

    <link href="/assets/css/dataTables.dataTables.css" rel="stylesheet" />
    <link rel="stylesheet" href="/assets/css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="/assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="/assets/fonts/line-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/349ee9c857.js" crossorigin="anonymous"></script>
    <link href="/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="/assets/css/corporate-ui-dashboard.css?v=1.0.0" rel="stylesheet" />

    <!--   Core JS Files   -->
    <script src="/assets/js/core/popper.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>
    <script src="/assets/js/plugins/jquery-3.7.1.min.js"></script>
    <script src="/assets/js/plugins/bootstrap-colorpicker.min.js"></script>
    <script src="/assets/js/plugins/bootstrap-colorpicker.min.js"></script>

    <!-- Control Center for Corporate UI Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="/assets/js/corporate-ui-dashboard.min.js?v=1.0.0"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        let myModal = null;
        let myInterval = null;
        let urlId = 0;
        $(document).ready(function() {
            $("#search").focus();
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });

            $( "#search" ).on( "keyup", function() {
                let oldSearch = $("#search").val();
                setTimeout(function(){
                    if (oldSearch === $("#search").val()) {
                        refreshMovies();
                    }
                },1000);
            } );

            $( ".category" ).on( "click", function(button) {
                $("#category").val($(this).attr("data-category"));
                $(".category").removeClass("category-active");
                $(this).addClass("category-active");
                refreshMovies();
            });

            myModal = new bootstrap.Modal(document.getElementById('myModal'), {
                backdrop: 'static', // Empêche la fermeture au clic en dehors de la modal
                keyboard: false     // Empêche la fermeture avec la touche Échap
            });
        });

        function refreshMovies(){
            let formats = {!! Auth::user()->getFormats() !!};

            $.ajax({
                type: "POST",
                url: "/search",
                data: {
                    "search": $("#search").val(),
                    "category": $("#category").val(),
                    "formats": formats
                },
            })
            .done(function(data) {
                $("#list").html(data);
            });
        }

        function addFavoriteSerie(button, name)
        {
            $(button).toggleClass("active");
            $.ajax({
                type: "POST",
                url: "/favorite_serie",
                data: {
                    "name": name,
                },
            })
            .done(function() {

            });
        }

        function addFavorite(button, id)
        {
            $(button).toggleClass("active");
            $.ajax({
                type: "GET",
                url: "/favorite/"+id,
            })
            .done(function() {

            });
        }

        function addWatched(button, id)
        {
            $(button).toggleClass("active");
            $.ajax({
                type: "GET",
                url: "/watched/"+id,
            })
            .done(function() {

            });
        }

        function remove(button, id)
        {
            $(button).toggleClass("active");
            $.ajax({
                type: "GET",
                url: "/remove/"+id,
            })
            .done(function() {
                $(button).parent().parent().parent().remove();
            });
        }

        function removeSerie(button, name)
        {
            $.ajax({
                type: "POST",
                url: "/remove_serie",
                data: {
                    "name": name,
                },
            })
            .done(function() {
                $(button).parent().parent().parent().remove();
            });
        }

        function addCounter(id)
        {
            let counter = parseInt($("#counter-"+id).attr('data-min'));
            $("#counter-"+id).attr('data-min', counter);
            $("#counter-"+id).html(formatHour(counter));
            $("#counter_title").html($("#urlname-"+id).html());
            $("#counter").html(formatHour(counter));
            myModal.show();
            urlId = id;

            $.ajax({
                type: "GET",
                url: "/counter/"+id+"/0",
            });

            myInterval = window.setInterval(
                function ()
                {
                    let newCounter = parseInt($("#counter-"+id).attr('data-min')) + 1;

                    let updatedUrl = document.getElementById("ahref-"+urlId).href.replace(/#(\d+)$/, (match, p1) => {
                        let newValue = parseInt(p1, 10) + 60;
                        return `#${newValue}`;
                    });
                    document.getElementById("ahref-"+urlId).href = updatedUrl;

                    $.ajax({
                        type: "GET",
                        url: "/counter/"+id+"/"+newCounter,
                    })
                    .done(function() {
                        $("#counter-"+id).attr('data-min', newCounter);
                        $("#counter-"+id).html(formatHour(newCounter));
                        $("#counter").html(formatHour(newCounter));
                    });
                }, 60000
            )
        }

        function formatHour(minutesTime)
        {
            if (minutesTime == 0) {return '';}
            let hours = parseInt(minutesTime / 60);
            let minutes = minutesTime % 60;
            if (minutes < 10){
                minutes = "0"+minutes;
            }

            return hours + ":" + minutes;
        }

        function setPaused(){
            clearInterval(myInterval);
            myModal.hide();
        }

        function setWatched(){
            clearInterval(myInterval);
            $("#eye-"+urlId).toggleClass("active");
            $("#counter-"+urlId).html("");
            $("#counter-"+urlId).attr('data-min', 0);
            myModal.hide();
            $.ajax({
                type: "GET",
                url: "/forceWatched/"+urlId,
            })
        }
    </script>

    <link rel="stylesheet" href="/fonts/fontawesome-all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="/assets/css/styles.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-app.sidebar />

    {{ $slot }}

    <div class="modal" tabindex="-1" role="dialog" id="myModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="counter_title"></h5>
                </div>
                <div class="modal-body">
                    <p><div id="counter"></div></p>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="setPaused()" class="btn btn-primary">Pause</button>
                    <button type="button" onclick="setWatched()" class="btn btn-primary">{{__("Ended")}}</button>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
</body>

</html>
