import os
from typing import Any

import joblib
import pandas as pd
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel


MODEL_PATH = "random_forest_inventory_model.pkl"


class InventoryInput(BaseModel):
    Category: str
    Region: str
    Price: float
    Discount: float
    Weather_Condition: str
    Holiday_Promotion: int
    Competitor_Pricing: float
    Seasonality: str


app = FastAPI(title="Inventory Prediction API", version="1.0.0")
model_bundle: dict[str, Any] | None = None


def load_model_bundle() -> dict[str, Any]:
    if not os.path.exists(MODEL_PATH):
        raise FileNotFoundError(
            f"Model file '{MODEL_PATH}' tidak ditemukan. Jalankan train_random_forest.py terlebih dahulu."
        )

    data = joblib.load(MODEL_PATH)
    if "model" not in data or "features" not in data:
        raise ValueError("Format file model tidak valid. Harus mengandung key 'model' dan 'features'.")

    return data


@app.on_event("startup")
def startup_event() -> None:
    global model_bundle
    model_bundle = load_model_bundle()


@app.get("/")
def healthcheck() -> dict[str, str]:
    return {"status": "ok", "message": "Inventory Prediction API is running"}


@app.post("/predict")
def predict(payload: InventoryInput) -> dict[str, float]:
    if model_bundle is None:
        raise HTTPException(status_code=500, detail="Model belum termuat di server.")

    model = model_bundle["model"]
    expected_features = model_bundle["features"]

    raw_row = {
        "Category": payload.Category,
        "Region": payload.Region,
        "Price": payload.Price,
        "Discount": payload.Discount,
        "Weather Condition": payload.Weather_Condition,
        "Holiday/Promotion": payload.Holiday_Promotion,
        "Competitor Pricing": payload.Competitor_Pricing,
        "Seasonality": payload.Seasonality,
    }

    input_df = pd.DataFrame([raw_row])
    encoded = pd.get_dummies(
        input_df,
        columns=["Category", "Region", "Weather Condition", "Seasonality"],
    )

    aligned = encoded.reindex(columns=expected_features, fill_value=0)
    prediction = float(model.predict(aligned)[0])

    return {"predicted_units_sold": prediction}
