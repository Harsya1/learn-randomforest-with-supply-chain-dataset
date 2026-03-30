<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InventoryController extends Controller
{
    public function index()
    {
        $engineStatus = $this->checkAiEngineStatus();

        return view('inventory.dashboard', [
            'engineStatus' => $engineStatus,
        ]);
    }

    public function predictAndDecide(Request $request)
    {
        $validated = $request->validate([
            'current_stock' => 'nullable|integer|min:0',
            'category' => 'nullable|string',
            'region' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'weather' => 'nullable|string',
            'holiday' => 'nullable|integer|min:0|max:1',
            'competitor_price' => 'nullable|numeric|min:0',
            'seasonality' => 'nullable|string',
        ]);

        // 1. Ambil input dari form web (atau database nantinya)
        $inputData = [
            'Category' => $validated['category'] ?? 'Furniture',
            'Region' => $validated['region'] ?? 'North',
            'Price' => (float) ($validated['price'] ?? 120.5),
            'Discount' => (float) ($validated['discount'] ?? 10.0),
            'Weather_Condition' => $validated['weather'] ?? 'Rainy',
            'Holiday_Promotion' => (int) ($validated['holiday'] ?? 0),
            'Competitor_Pricing' => (float) ($validated['competitor_price'] ?? 115.0),
            'Seasonality' => $validated['seasonality'] ?? 'Winter',
        ];

        // Variabel gudang saat ini (Nanti ini diambil dari Database MySQL)
        $currentStock = (int) ($validated['current_stock'] ?? 100);
        $leadTimeDays = 3; // Waktu pengiriman dari supplier (contoh 3 hari)

        try {
            // 2. Tembak API Python (FastAPI)
            $response = Http::timeout(10)->post($this->fastApiBaseUrl() . '/predict', $inputData);

            if ($response->successful()) {
                // 3. Ambil angka hasil prediksi
                $predictedSalesPerDay = (float) ($response->json()['predicted_units_sold'] ?? 0);

                // 4. LOGIKA SPK (SISTEM PENDUKUNG KEPUTUSAN)
                // Berapa barang yang diprediksi akan terjual selama masa tunggu pengiriman?
                $expectedDemand = $predictedSalesPerDay * $leadTimeDays;

                // Jika sisa stok di gudang kurang dari atau sama dengan prediksi permintaan...
                if ($currentStock <= $expectedDemand) {
                    $spkDecision = 'RESTOCK SEKARANG';
                    $alertColor = 'text-red-700 bg-red-100 border-red-200';
                    $recommendedOrder = (int) ceil($expectedDemand - $currentStock + 50); // Tambah safety stock 50
                } else {
                    $spkDecision = 'STOK AMAN';
                    $alertColor = 'text-emerald-700 bg-emerald-100 border-emerald-200';
                    $recommendedOrder = 0;
                }

                return back()->withInput()->with([
                    'success' => true,
                    'predicted_sales' => round($predictedSalesPerDay),
                    'expected_demand' => round($expectedDemand),
                    'current_stock' => $currentStock,
                    'spk_decision' => $spkDecision,
                    'alert_color' => $alertColor,
                    'recommended_order' => $recommendedOrder,
                ]);
            }

            return back()->withInput()->with('error', 'Gagal terhubung ke AI Engine (FastAPI).');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error: Pastikan server Python berjalan. Detail: ' . $e->getMessage());
        }
    }

    private function fastApiBaseUrl(): string
    {
        return rtrim((string) env('FASTAPI_URL', 'http://127.0.0.1:8001'), '/');
    }

    private function checkAiEngineStatus(): array
    {
        try {
            $response = Http::timeout(2)->get($this->fastApiBaseUrl() . '/');

            if ($response->successful()) {
                return [
                    'online' => true,
                    'message' => 'AI Engine Online',
                ];
            }
        } catch (\Throwable $e) {
            // Ignore exception and fall back to offline state.
        }

        return [
            'online' => false,
            'message' => 'AI Engine Offline',
        ];
    }
}
