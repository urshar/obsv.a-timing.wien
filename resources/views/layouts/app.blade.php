<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin')</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
<div class="min-h-screen">
    @include('layouts._header')

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('layouts._import_alert')
        @include('layouts._validation_errors')

        @yield('content')
    </main>

    @include('layouts._footer')
</div>
</body>
</html>
