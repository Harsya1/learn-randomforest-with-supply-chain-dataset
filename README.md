# Random Forest Supply Chain + Laravel SPK Dashboard

Proyek ini menggabungkan:
- Training model Random Forest (Python, scikit-learn)
- API prediksi (FastAPI)
- Dashboard dan logika SPK restock (Laravel)

## Struktur Proyek

- `train_random_forest.py`: script training model
- `fastapi_inventory_api.py`: API prediksi (`/predict`)
- `requirements.txt`: dependency Python
- `laravel-app/`: aplikasi Laravel (controller, route, dashboard)

## Prasyarat

- Python 3.13+ (atau kompatibel)
- PHP 8.2+ (disarankan 8.3)
- Composer
- Dataset `retail_store_inventory.csv` atau `retail_store_inventory.xlsx` di root project

## 1) Setup Python dan Train Model

Dari root project:

```powershell
# (Opsional) buat virtual environment
python -m venv .venv

# Aktifkan venv (PowerShell)
.\.venv\Scripts\Activate.ps1

# Install dependency
pip install -r requirements.txt

# Training model
python train_random_forest.py
```

Output training akan membuat file:
- `random_forest_inventory_model.pkl`

## 2) Jalankan FastAPI (AI Engine)

Dari root project:

```powershell
.\.venv\Scripts\python.exe -m uvicorn fastapi_inventory_api:app --host 127.0.0.1 --port 8001
```

Cek health endpoint:
- `GET http://127.0.0.1:8001/`

Endpoint prediksi:
- `POST http://127.0.0.1:8001/predict`

Contoh body JSON:

```json
{
  "Category": "Furniture",
  "Region": "North",
  "Price": 120.5,
  "Discount": 10.0,
  "Weather_Condition": "Rainy",
  "Holiday_Promotion": 0,
  "Competitor_Pricing": 115.0,
  "Seasonality": "Winter"
}
```

## 3) Jalankan Laravel Dashboard

Dari folder Laravel:

```powershell
cd laravel-app
composer install
copy .env.example .env
php artisan key:generate
php artisan serve --host=127.0.0.1 --port=8000
```

Pastikan di `laravel-app/.env` ada:

```env
FASTAPI_URL=http://127.0.0.1:8001
```

Buka dashboard:
- `http://127.0.0.1:8000/inventory`

## 4) Alur SPK yang Berjalan

- Laravel mengambil input dari form dashboard
- Laravel memanggil FastAPI `/predict`
- FastAPI mengembalikan `predicted_units_sold`
- Laravel menghitung:
  - `expectedDemand = predictedSalesPerDay * leadTimeDays`
- Keputusan SPK:
  - `RESTOCK SEKARANG` jika `currentStock <= expectedDemand`
  - `STOK AMAN` jika sebaliknya

## Troubleshooting

### cURL error 7 / gagal connect ke FastAPI

- Pastikan FastAPI sedang jalan di port 8001
- Pastikan `FASTAPI_URL` Laravel mengarah ke `http://127.0.0.1:8001`
- Jangan pakai port yang sama untuk Laravel dan FastAPI

### Could not open input file: artisan

- Pastikan perintah `php artisan ...` dijalankan dari folder `laravel-app`