<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Transportation Management System</title>
    <meta name="description" content="Aplikasi pengelolaan logistik, shipment order, driver assignment, dan POD terintegrasi.">

    <!-- CSS and JS Bundled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark text-white">
    <div id="app"></div>
</body>
</html>
