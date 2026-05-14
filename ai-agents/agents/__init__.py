from agents.verifikasi_agent import VerifikasiAgent
from agents.prediksi_agent import PrediksiAgent
from agents.rekomendasi_agent import RekomendasiAgent
from agents.peringatan_agent import PeringatanAgent
from agents.generator_agent import GeneratorAgent
from agents.integrasi_agent import IntegrasiAgent


_AGENT_REGISTRY = {
    "verifikasi": VerifikasiAgent,
    "prediksi": PrediksiAgent,
    "rekomendasi": RekomendasiAgent,
    "peringatan": PeringatanAgent,
    "generator": GeneratorAgent,
    "integrasi": IntegrasiAgent,
}


def get_agent(name: str):
    cls = _AGENT_REGISTRY.get(name)
    if cls is None:
        return None
    return cls()
