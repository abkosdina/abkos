<!DOCTYPE html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ورود به مستروام' }}</title>

    <link rel="stylesheet" href="{{ asset('cdn/Vazirmatn-font-face.css') }}">

    @vite(['resources/css/auth/auth.css', 'resources/js/shared/bootstrap.js'])
</head>
<body class="min-h-screen mw-page-bg mw-noise font-[Vazirmatn] text-gray-800 antialiased">
    {{ $slot }}
</body>
</html>