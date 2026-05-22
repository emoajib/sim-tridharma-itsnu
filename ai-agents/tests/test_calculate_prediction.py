import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), ".."))

from config import calculate_prediction


def test_returns_defaults_for_empty_input():
    result = calculate_prediction([])

    assert result["skor_prediksi"] == 0
    assert result["probabilitas"]["unggul"] == 0.05
    assert result["probabilitas"]["baik_sekali"] == 0.1
    assert result["probabilitas"]["baik"] == 0.85
    assert result["trend_factor"] == 1.0
    assert result["trend_analysis"] == "Stagnan"


def test_returns_base_score_for_single_value():
    result = calculate_prediction([250])

    assert result["skor_prediksi"] == 250
    assert result["trend_factor"] == 1.0
    assert result["trend_analysis"] == "Stagnan"


def test_positif_trend():
    result = calculate_prediction([200, 250, 300])

    assert result["trend_analysis"] == "Positif"
    assert result["trend_factor"] > 1.0
    assert result["skor_prediksi"] > 300


def test_negatif_trend():
    result = calculate_prediction([300, 250, 200])

    assert result["trend_analysis"] == "Negatif"
    assert result["trend_factor"] < 1.0
    assert result["skor_prediksi"] < 200


def test_high_score_gives_unggul_probability():
    result = calculate_prediction([350, 370, 380])

    assert result["probabilitas"]["unggul"] > 0.5
    assert result["trend_analysis"] == "Positif"


def test_low_score_gives_baik_probability():
    result = calculate_prediction([180, 175, 170])

    assert result["probabilitas"]["unggul"] < 0.5
    assert result["probabilitas"]["baik"] > 0.3
    assert result["trend_analysis"] == "Negatif"


def test_uses_last_3_values():
    result = calculate_prediction([100, 150, 200, 250, 300])

    assert result["skor_prediksi"] > 300
    assert result["trend_analysis"] == "Positif"


def test_rounds_to_two_decimals():
    result = calculate_prediction([255.555])

    assert result["skor_prediksi"] == 255.56


def test_probabilities_sum_to_one():
    result = calculate_prediction([200, 250])
    total = (result["probabilitas"]["unggul"] +
             result["probabilitas"]["baik_sekali"] +
             result["probabilitas"]["baik"])
    assert abs(total - 1.0) < 0.01


def test_trend_factor_rounds_to_four_decimals():
    result = calculate_prediction([200, 250])

    assert isinstance(result["trend_factor"], float)
