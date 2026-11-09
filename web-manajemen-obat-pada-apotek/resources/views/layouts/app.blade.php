<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Apotek</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <!-- Sidebar Navigasi (Sesuai Mockup) -->
    @include('layouts.sidebar')

    <!-- Konten Utama -->
    <main class="flex-1 overflow-y-auto">
        @yield('content')
    </main>

</body>
</html>