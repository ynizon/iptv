<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/fonts/fontawesome-all.min.css">

    <style>
        input{
            color:#FF2D20;;
        }

        a {
            text-decoration: none;
            text-align: center;
            display:block;
            padding:5px;
        }

        a img{
            max-width: 120px;
            text-align: center;
        }
        .row{
            padding:5px;
        }

        h5{padding:5px;}

        .left{
            text-align: left;
        }

        .category-active{
            color:#FF2D20;
        }

        .fa-eye.active{
            color: #8511ef;
        }
        .fa-heart.active{
            color: #8c1911;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
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

            $( ".formats" ).on( "click", function() {
                refreshMovies();
            });

            $( ".category" ).on( "click", function(button) {
                $("#category").val($(this).attr("data-category"));
                $(".category").removeClass("category-active");
                $(this).addClass("category-active");
                refreshMovies();
            });
        });

        function refreshMovies(){
            let formats = [];
            let inputElements = document.getElementsByClassName('formats');
            for(let i=0; inputElements[i]; ++i){
                if(inputElements[i].checked){
                    formats.push(inputElements[i].value);
                }
            }

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
    </script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="font-sans antialiased dark:bg-black dark:text-white/50">
<div class="row">
    {{ $slot }}
</div>
</body>
</html>
