import os

import joblib
import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.model_selection import train_test_split


def main() -> None:
    dataset_candidates = ["retail_store_inventory.csv", "retail_store_inventory.xlsx"]
    model_output_path = "random_forest_inventory_model.pkl"

    print("1. Membaca dataset...")
    dataset_path = next((p for p in dataset_candidates if os.path.exists(p)), None)
    if dataset_path is None:
        raise FileNotFoundError(
            "Dataset tidak ditemukan. Gunakan salah satu nama berikut di folder saat ini "
            f"({os.getcwd()}): {dataset_candidates}"
        )

    # Load dataset
    if dataset_path.endswith(".csv"):
        df = pd.read_csv(dataset_path)
    elif dataset_path.endswith(".xlsx"):
        df = pd.read_excel(dataset_path)
    else:
        raise ValueError(f"Format dataset tidak didukung: {dataset_path}")

    print("2. Melakukan Preprocessing Data...")
    # Menentukan Target (Y) yang ingin diprediksi
    y = df["Units Sold"]

    # Menentukan Fitur (X) yang mempengaruhi penjualan
    # Kita abaikan kolom seperti Date, Store ID, Product ID untuk global model saat ini
    features = [
        "Category",
        "Region",
        "Price",
        "Discount",
        "Weather Condition",
        "Holiday/Promotion",
        "Competitor Pricing",
        "Seasonality",
    ]

    missing_columns = [col for col in features + ["Units Sold"] if col not in df.columns]
    if missing_columns:
        raise ValueError(f"Kolom berikut tidak ditemukan di dataset: {missing_columns}")

    X = df[features]

    # Mengubah data teks (Kategorikal) menjadi angka menggunakan One-Hot Encoding
    # Random Forest di scikit-learn membutuhkan input berupa angka
    X_encoded = pd.get_dummies(
        X, columns=["Category", "Region", "Weather Condition", "Seasonality"]
    )

    print("3. Membagi data menjadi Data Latih (80%) dan Data Uji (20%)...")
    X_train, X_test, y_train, y_test = train_test_split(
        X_encoded, y, test_size=0.2, random_state=42
    )

    print(
        "4. Melatih Model Random Forest Regressor (Ini mungkin memakan waktu beberapa detik/menit)..."
    )
    # Menggunakan 100 decision trees (n_estimators)
    rf_model = RandomForestRegressor(n_estimators=100, random_state=42, n_jobs=-1)
    rf_model.fit(X_train, y_train)

    print("5. Melakukan Prediksi pada Data Uji...")
    y_pred = rf_model.predict(X_test)

    print("6. Evaluasi Akurasi Model:")
    # Menghitung seberapa besar error prediksinya
    mae = mean_absolute_error(y_test, y_pred)
    rmse = np.sqrt(mean_squared_error(y_test, y_pred))
    r2 = r2_score(y_test, y_pred)

    print(f"- Mean Absolute Error (MAE): {mae:.2f} unit")
    print(f"- Root Mean Squared Error (RMSE): {rmse:.2f} unit")
    print(f"- R-squared (R2 Score): {r2:.4f} (Mendekati 1.0 berarti model sangat baik)")

    print("\n7. Menyimpan Model untuk Digunakan di Backend API Nanti...")
    # Menyimpan model beserta struktur kolom encoded agar bisa di-load oleh FastAPI nanti
    model_data = {"model": rf_model, "features": X_encoded.columns.tolist()}
    joblib.dump(model_data, model_output_path)
    print(f"Model berhasil disimpan dengan nama '{model_output_path}'!")


if __name__ == "__main__":
    main()
