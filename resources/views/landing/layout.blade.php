<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'مستر وام | خرید و فروش امتیاز وام بانک‌ها' }}</title>

    <link rel="stylesheet" href="{{ asset('cdn/Vazirmatn-font-face.css') }}">
    @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/landing.js'])

    <script defer src="{{ asset('cdn/alpinejs-3.14.1.min.js') }}"></script>
    <script src="{{ asset('cdn/axios.min.js') }}"></script>
</head>
<body class="bg-white text-ink-900 antialiased font-vazir" x-data="siteApp()" x-init="init()">
    @yield('content')
</body>
</html>
