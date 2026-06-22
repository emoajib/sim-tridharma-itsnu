# DATABASE ERD - SIM TRIDHARMA ITSNU

Generated: 2026-06-22
Database: PostgreSQL 17

## Model Inventory

### Master Data (m_)
| Table | Model | Description |
|-------|-------|-------------|
| m_fakultas | Fakultas | Faculty list |
| m_prodi | Prodi | Study programs |
| m_dosen | Dosen | Lecturers |
| m_mahasiswa | Mahasiswa | Students |
| m_alumni | Alumni | Alumni |
| m_mitra | Mitra | Partners |
| m_mata_kuliah | MataKuliah | Courses |
| m_kurikulum | Kurikulum | Curriculum |
| m_sarana | Sarana | Facilities |
| m_penunjang | Penunjang | Supporting resources |
| m_lembaga_akreditasi | LembagaAkreditasi | Accreditation agencies |
| m_instrumen_akreditasi | InstrumenAkreditasi | Accreditation instruments |
| m_indikator_akreditasi | IndikatorAkreditasi | Accreditation indicators |
| m_kriteria_standar | KriteriaStandar | Standard criteria |
| m_standar_mutu | StandarMutu | Quality standards |
| m_template | Template | Document templates |
| m_periode_akademik | PeriodeAkademik | Academic periods |
| m_jenis_iku | JenisIku | IKU types |

### Transactions (trx_)
| Table | Model | Description |
|-------|-------|-------------|
| trx_publikasi | Publikasi | Publications |
| trx_penelitian | Penelitian | Research |
| trx_pkm | Pkm | Community service |
| trx_bkd | Bkd | Lecturer workload |
| trx_rps | Rps | Lesson plans |
| trx_edps | Edps | EDPS documents |
| trx_kegiatan_pendidikan | KegiatanPendidikan | Educational activities |
| trx_kegiatan_penunjang | KegiatanPenunjang | Supporting activities |
| trx_kegiatan_pkm | KegiatanPkm | PKM activity details |
| trx_kegiatan_penelitian | KegiatanPenelitian | Research activity details |
| trx_kerjasama | Kerjasama | Cooperation |
| trx_keuangan | Keuangan | Finance |
| trx_proposal_kegiatan | ProposalKegiatan | Activity proposals |
| trx_risk_register | RiskRegister | Risk register |
| trx_portofolio | Portofolio | Portfolio |
| trx_rekomendasi | Rekomendasi | Recommendations |
| trx_rkat | Rkat | Budget plan |
| trx_iku | Iku | Performance indicators |
| trx_rkat_item | RkatItem | RKAT line items |
| trx_rkat_realisasi | RkatRealisasi | RKAT realization |
| trx_cascading_iku | CascadingIku | IKU cascading |
| trx_penjaminan_mutu | PenjaminanMutu | Quality assurance |

### SPMI (spmi_)
| Table | Model | Description |
|-------|-------|-------------|
| spmi_cycles | SpmiCycle | SPMI cycles |
| m_spmi_dokumen | SpmiDokumen | SPMI documents |
| trx_rtm | Rtm | RTM meetings |
| trx_rtm_action_items | RtmActionItem | RTM action items |
| trx_tindakan_koreksi | TindakanKoreksi | Corrective actions |
| trx_audit_mutu | AuditMutu | Quality audits |
| trx_capa | Capa | Corrective/preventive actions |

### Knowledge Base
| Table | Model | Description |
|-------|-------|-------------|
| knowledge_base_documents | KnowledgeBaseDocument | KB documents |
| knowledge_base_chunks | KnowledgeBaseChunk | KB vector chunks |

### AI Agents
| Table | Model | Description |
|-------|-------|-------------|
| agent_execution_log | AgentExecutionLog | Agent execution logs |
| app_brain_entities | AppBrainEntity | Knowledge graph entities |
| app_brain_relations | AppBrainRelation | Knowledge graph relations |
| app_brain_snapshots | AppBrainSnapshot | Knowledge graph snapshots |

### Security & Audit
| Table | Model | Description |
|-------|-------|-------------|
| security_audit_logs | SecurityAuditLog | Security audit trail |
| import_history | ImportHistory | Data import history |
| personal_access_tokens | (Sanctum) | API tokens |
| cache | - | Cache store |
| cache_locks | - | Cache locks |
| sessions | - | User sessions |
| jobs | - | Queue jobs |
| job_batches | - | Job batches |
| failed_jobs | - | Failed jobs |
| migrations | - | Migration tracking |
| password_reset_tokens | - | Password resets |
| notification_logs | - | Notification log |

### Users & Authorization
| Table | Model | Description |
|-------|-------|-------------|
| users | User | System users |
| roles | Role | User roles |
| permissions | Permission | User permissions |
| role_has_permissions | - | Role-permission pivot |
| model_has_roles | - | User-role pivot |
| model_has_permissions | - | User-permission pivot |
| two_factor_configs | - | 2FA configuration |

### Integration (SINTA)
| Table | Model | Description |
|-------|-------|-------------|
| integrasi_sinta_publikasi | IntegrasiSintaPublikasi | SINTA publication sync |
| integrasi_sinta_penelitian | IntegrasiSintaPenelitian | SINTA research sync |
| integrasi_sinta_pkm | IntegrasiSintaPkm | SINTA community service sync |
| integrasi_sinta_log | IntegrasiSintaLog | SINTA sync logs |

### Surveys & Questionnaires
| Table | Model | Description |
|-------|-------|-------------|
| kuisioner_tracer | KuisionerTracer | Tracer questionnaires |
| survey_spmi | SurveySpmi | SPMI surveys |
| survey_spmi_pertanyaan | SurveySpmiPertanyaan | Survey questions |
| tracer_jawaban | TracerJawaban | Tracer answers |
| survey_spmi_jawaban | SurveySpmiJawaban | Survey answers |

### Verifikasi & Prediksi
| Table | Model | Description |
|-------|-------|-------------|
| verifikasi_data | VerifikasiData | Data verification |
| prediksi_akreditasi | PrediksiAkreditasi | Accreditation prediction |

> **Note**: Estimates ~85 material tables. Exact count varies by environment.
> Regenerate with: `php artisan db:show --dump` or use pgAdmin ERD tool.