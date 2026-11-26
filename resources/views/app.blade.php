<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ config('brand.color') }}">
    <title inertia>{{ config('brand.name') }}</title>
    <style>:root { --brand: {{ config('brand.color') }}; }</style>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
