<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Chain AI Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-a: #f2efe8;
            --bg-b: #d8ebff;
            --ink: #0f172a;
            --card: #ffffffcc;
            --accent: #0f766e;
            --accent-strong: #115e59;
            --warm: #f97316;
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 15%, #ffd7a5 0%, transparent 35%),
                radial-gradient(circle at 90% 0%, #c3d7ff 0%, transparent 30%),
                linear-gradient(145deg, var(--bg-a), var(--bg-b));
        }

        .mono {
            font-family: 'IBM Plex Mono', monospace;
        }

        .glass-card {
            background: var(--card);
            backdrop-filter: blur(10px);
        }

        .rise {
            animation: rise .65s ease-out both;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="min-h-screen px-4 py-6 sm:px-6 sm:py-8 lg:px-10 lg:py-10">
    <main class="mx-auto max-w-6xl space-y-6">
        <section class="glass-card rise overflow-hidden rounded-3xl border border-white/70 p-6 shadow-2xl shadow-slate-900/10 sm:p-8">
            <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="mono mb-2 inline-block rounded-full bg-slate-900 px-3 py-1 text-xs font-medium uppercase tracking-wider text-white">Supply Chain Control Room</p>
                    <h1 class="text-2xl font-bold leading-tight text-slate-900 sm:text-4xl">AI Dashboard Prediksi Stok dan Restock SPK</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-700 sm:text-base">Pantau kesehatan AI engine, simulasikan demand, lalu putuskan restock berdasarkan estimasi penjualan dan lead time supplier.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $engineStatus['online'] ? 'border-emerald-200 bg-emerald-100 text-emerald-700' : 'border-red-200 bg-red-100 text-red-700' }}">
                        {{ $engineStatus['message'] }}
                    </span>
                    <a href="{{ route('inventory.index') }}" class="rounded-full border border-slate-300 bg-white/80 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-400 hover:bg-white">
                        Cek Ulang Status
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="glass-card rise rounded-2xl border border-white/70 p-4 shadow-lg shadow-slate-900/5" style="animation-delay: .08s">
                <p class="mono text-xs uppercase tracking-wider text-slate-500">Current Stock</p>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ session('current_stock', old('current_stock', 100)) }}</p>
                <p class="mt-1 text-xs text-slate-600">Unit tersedia di gudang</p>
            </article>
            <article class="glass-card rise rounded-2xl border border-white/70 p-4 shadow-lg shadow-slate-900/5" style="animation-delay: .14s">
                <p class="mono text-xs uppercase tracking-wider text-slate-500">Prediksi / Hari</p>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ session('predicted_sales', '-') }}</p>
                <p class="mt-1 text-xs text-slate-600">Estimasi unit terjual</p>
            </article>
            <article class="glass-card rise rounded-2xl border border-white/70 p-4 shadow-lg shadow-slate-900/5" style="animation-delay: .2s">
                <p class="mono text-xs uppercase tracking-wider text-slate-500">Demand Lead Time</p>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ session('expected_demand', '-') }}</p>
                <p class="mt-1 text-xs text-slate-600">Perkiraan 3 hari ke depan</p>
            </article>
            <article class="glass-card rise rounded-2xl border border-white/70 p-4 shadow-lg shadow-slate-900/5" style="animation-delay: .26s">
                <p class="mono text-xs uppercase tracking-wider text-slate-500">Rekomendasi Order</p>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ session('recommended_order', 0) }}</p>
                <p class="mt-1 text-xs text-slate-600">Unit yang disarankan</p>
            </article>
        </section>

        <section class="grid gap-6 lg:grid-cols-5">
            <div class="glass-card rise rounded-3xl border border-white/70 p-6 shadow-2xl shadow-slate-900/10 lg:col-span-3" style="animation-delay: .32s">
                <h2 class="text-xl font-bold text-slate-900">Form Simulasi Prediksi</h2>
                <p class="mt-1 text-sm text-slate-600">Input parameter produk dan kondisi pasar untuk menghasilkan rekomendasi restock.</p>

                <form action="{{ route('inventory.predict') }}" method="POST" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @csrf

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Sisa Stok Saat Ini</label>
                        <input type="number" name="current_stock" value="{{ old('current_stock', 100) }}" min="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-700 focus:ring-2 focus:ring-teal-200">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kategori Produk</label>
                        <select name="category" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-700 focus:ring-2 focus:ring-teal-200">
                            @php($category = old('category', 'Furniture'))
                            <option value="Furniture" @selected($category === 'Furniture')>Furniture</option>
                            <option value="Electronics" @selected($category === 'Electronics')>Electronics</option>
                            <option value="Toys" @selected($category === 'Toys')>Toys</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Region</label>
                        <input type="text" name="region" value="{{ old('region', 'North') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-700 focus:ring-2 focus:ring-teal-200">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Harga</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', 120.5) }}" min="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-700 focus:ring-2 focus:ring-teal-200">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Diskon</label>
                        <input type="number" step="0.01" name="discount" value="{{ old('discount', 10.0) }}" min="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-700 focus:ring-2 focus:ring-teal-200">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Weather</label>
                        <select name="weather" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-700 focus:ring-2 focus:ring-teal-200">
                            @php($weather = old('weather', 'Rainy'))
                            <option value="Sunny" @selected($weather === 'Sunny')>Sunny</option>
                            <option value="Rainy" @selected($weather === 'Rainy')>Rainy</option>
                            <option value="Snowy" @selected($weather === 'Snowy')>Snowy</option>
                            <option value="Cloudy" @selected($weather === 'Cloudy')>Cloudy</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Holiday Promotion</label>
                        <select name="holiday" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-700 focus:ring-2 focus:ring-teal-200">
                            @php($holiday = (string) old('holiday', 0))
                            <option value="0" @selected($holiday === '0')>0 - Tidak Ada Promo</option>
                            <option value="1" @selected($holiday === '1')>1 - Ada Promo/Hari Libur</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Competitor Pricing</label>
                        <input type="number" step="0.01" name="competitor_price" value="{{ old('competitor_price', 115.0) }}" min="0" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-700 focus:ring-2 focus:ring-teal-200">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Seasonality</label>
                        <select name="seasonality" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-teal-700 focus:ring-2 focus:ring-teal-200">
                            @php($seasonality = old('seasonality', 'Winter'))
                            <option value="Winter" @selected($seasonality === 'Winter')>Winter</option>
                            <option value="Spring" @selected($seasonality === 'Spring')>Spring</option>
                            <option value="Summer" @selected($seasonality === 'Summer')>Summer</option>
                            <option value="Autumn" @selected($seasonality === 'Autumn')>Autumn</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <button type="submit" class="w-full rounded-xl bg-teal-700 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-white transition hover:-translate-y-0.5 hover:bg-teal-800">
                            Analisis Prediksi dan Rekomendasi
                        </button>
                    </div>
                </form>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <aside class="space-y-4 lg:col-span-2">
                <div class="glass-card rise rounded-3xl border border-white/70 p-5 shadow-2xl shadow-slate-900/10" style="animation-delay: .38s">
                    <h3 class="text-lg font-bold text-slate-900">Decision Panel</h3>

                    @if(session('success'))
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-700">
                                Keputusan SPK:
                                <p class="mt-1 text-xl font-extrabold text-slate-900">{{ session('spk_decision') }}</p>
                            </div>
                            <div class="rounded-xl border p-4 {{ session('alert_color') }}">
                                <p class="font-semibold">Interpretasi Sistem</p>
                                <p class="mt-1 text-sm">Model memperkirakan demand lead-time sebesar <strong>{{ session('expected_demand') }} unit</strong> dibanding stok saat ini <strong>{{ session('current_stock') }} unit</strong>.</p>
                                @if(session('recommended_order') > 0)
                                    <p class="mt-2 text-sm">Saran order: <strong>{{ session('recommended_order') }} unit</strong>.</p>
                                @endif
                            </div>
                        </div>
                    @elseif(session('error'))
                        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @else
                        <p class="mt-4 rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600">Belum ada hasil analisis. Jalankan simulasi dari form untuk menampilkan rekomendasi restock.</p>
                    @endif
                </div>

                <div class="glass-card rise rounded-3xl border border-white/70 p-5 shadow-2xl shadow-slate-900/10" style="animation-delay: .44s">
                    <h3 class="text-lg font-bold text-slate-900">Catatan Operasional</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-700">
                        <li>Lead time diasumsikan 3 hari untuk seluruh supplier.</li>
                        <li>Safety stock otomatis ditambahkan saat status restock aktif.</li>
                        <li>Perbarui harga dan kompetitor pricing agar output lebih akurat.</li>
                    </ul>
                    <div class="mt-4 rounded-xl bg-slate-900 p-3 text-xs text-slate-100">
                        <span class="mono">Endpoint AI:</span> {{ rtrim((string) env('FASTAPI_URL', 'http://127.0.0.1:8001'), '/') }}/predict
                    </div>
                </div>
            </aside>
        </section>
    </main>
</body>
</html>
