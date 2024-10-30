<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    @if (config('app.is_demo'))
        <title itemprop="name">
            {{config("app.name")}}
        </title>
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@enpix" />
        <meta name="twitter:creator" content="@enpix" />
        <meta name="twitter:title" content="{{config("app.name")}}" />
        <meta name="twitter:description" content="Tasks Remember" />
        <meta name="twitter:image"
            content="https://s3.amazonaws.com/creativetim_bucket/products/737/original/corporate-ui-dashboard-laravel.jpg?1695288974" />
        <meta name="twitter:url" content="{{config("app.url")}}" />
        <meta name="description" content="Tasks Remember">
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
    @endif
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
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/349ee9c857.js" crossorigin="anonymous"></script>
    <link href="/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="/assets/css/corporate-ui-dashboard.css?v=1.0.0" rel="stylesheet" />
</head>

<body class="">

    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 bg-slate-900 fixed-start invisible" id="sidenav-main">
        <div class="sidenav-header">
        </div>
        <div class="collapse navbar-collapse px-4  w-auto " id="sidenav-collapse-main">

        </div>
    </aside>

    {{ $slot }}
    <!--   Core JS Files   -->
    <script src="/assets/js/core/popper.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>
    <script src="/assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="/assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Corporate UI Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="/assets/js/corporate-ui-dashboard.min.js?v=1.0.0"></script>
</body>

</html>
