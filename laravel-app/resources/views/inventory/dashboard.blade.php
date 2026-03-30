<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Chain AI Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 p-6 md:p-10">
    <div class="mx-auto max-w-4xl rounded-xl bg-white p-6 shadow-lg md:p-8">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <h1 class="text-2xl font-bold text-slate-800 md:text-3xl">AI Supply Chain and Restock SPK</h1>
            <div class="flex items-center gap-2">
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $engineStatus['online'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                    {{ $engineStatus['message'] }}
                </span>
                <a href="{{ route('inventory.index') }}" class="rounded border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    Cek Ulang Status
                </a>
            </div>
        </div>

        <form action="{{ route('inventory.predict') }}" method="POST" class="mb-8 grid grid-cols-1 gap-4 rounded-lg border bg-slate-50 p-4 md:grid-cols-2">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Sisa Stok Saat Ini</label>
                <input type="number" name="current_stock" value="{{ old('current_stock', 100) }}" class="mt-1 w-full rounded border p-2" min="0">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Kategori Produk</label>
                <select name="category" class="mt-1 w-full rounded border p-2">
                    @php($category = old('category', 'Furniture'))
                    <option value="Furniture" @selected($category === 'Furniture')>Furniture</option>
                    <option value="Electronics" @selected($category === 'Electronics')>Electronics</option>
                    <option value="Toys" @selected($category === 'Toys')>Toys</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Region</label>
                <input type="text" name="region" value="{{ old('region', 'North') }}" class="mt-1 w-full rounded border p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Harga</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', 120.5) }}" class="mt-1 w-full rounded border p-2" min="0">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Diskon</label>
                <input type="number" step="0.01" name="discount" value="{{ old('discount', 10.0) }}" class="mt-1 w-full rounded border p-2" min="0">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Weather</label>
                <input type="text" name="weather" value="{{ old('weather', 'Rainy') }}" class="mt-1 w-full rounded border p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Holiday/Promotion (0 atau 1)</label>
                <input type="number" name="holiday" value="{{ old('holiday', 0) }}" class="mt-1 w-full rounded border p-2" min="0" max="1">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Competitor Pricing</label>
                <input type="number" step="0.01" name="competitor_price" value="{{ old('competitor_price', 115.0) }}" class="mt-1 w-full rounded border p-2" min="0">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Seasonality</label>
                <input type="text" name="seasonality" value="{{ old('seasonality', 'Winter') }}" class="mt-1 w-full rounded border p-2">
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">
                    Analisis Prediksi dan Rekomendasi
                </button>
            </div>
        </form>

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-lg border bg-slate-50 p-6 shadow-inner">
                <h2 class="mb-4 text-lg font-bold text-slate-800">Hasil Analisis Algoritma Random Forest</h2>
                <ul class="mb-4 space-y-2 text-slate-700">
                    <li>Sisa Stok Gudang: <strong>{{ session('current_stock') }} unit</strong></li>
                    <li>Prediksi Penjualan (Per Hari): <strong>{{ session('predicted_sales') }} unit</strong></li>
                    <li>Estimasi Barang Keluar Selama Lead Time (3 Hari): <strong>{{ session('expected_demand') }} unit</strong></li>
                </ul>

                <div class="rounded-md border p-4 {{ session('alert_color') }}">
                    <h3 class="text-xl font-extrabold">{{ session('spk_decision') }}</h3>
                    @if(session('recommended_order') > 0)
                        <p class="mt-2 text-sm">Sistem merekomendasikan pemesanan ulang sebanyak: <strong>{{ session('recommended_order') }} unit</strong></p>
                    @endif
                </div>
            </div>
        @elseif(session('error'))
            <div class="rounded-md border border-red-200 bg-red-50 p-4 text-red-700">
                {{ session('error') }}
            </div>
        @endif
    </div>
</body>
</html>
