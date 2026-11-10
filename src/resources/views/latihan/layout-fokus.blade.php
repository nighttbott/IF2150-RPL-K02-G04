<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul') · {{ config('app.name', 'Ngaksara') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', ...($skrip ?? [])])
</head>
<body>
@yield('isi')
</body>
</html>
