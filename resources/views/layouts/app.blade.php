<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KDMP</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-slate-100">

<div class="flex min-h-screen">

    @include('layouts.sidebar')

    <main class="flex-1">

        @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        {{ $slot }}

    </main>

</div>

</body>
</html>
