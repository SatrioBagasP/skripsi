<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} | {{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center min-h-screen">

    <div class="text-center p-8 bg-white shadow-2xl rounded-2xl max-w-lg animate-fade-in">
        <div class="flex justify-center mb-6">
            <!-- Icon -->
            <svg class="w-24 h-24 text-indigo-500 animate-bounce" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>

        <h1 class="text-5xl font-extrabold text-gray-800 mb-4">{{ $code }}</h1>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">{{ $title }}</h2>
        <p class="text-lg text-gray-600 mb-6">{{ $message }}</p>

        <div class="flex justify-center gap-4">
            <a href="{{ url('/') }}"
                class="px-6 py-3 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700 transition">
                Kembali ke Beranda
            </a>
            <a href="{{ url()->previous() }}"
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl shadow hover:bg-gray-300 transition">
                Kembali
            </a>
        </div>
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.6s ease-out forwards;
        }
    </style>

</body>

</html>
