<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../api/axios'

const emit = defineEmits(['logout'])

// State
const colonies = ref([])
const loading = ref(false)
const actionLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const validationErrors = ref({})
const searchQuery = ref('')

// Modal State
const showModal = ref(false)
const isEditMode = ref(false)
const editingId = ref(null)

// Form Data
const form = ref({
  kode_fragmen: '',
  spesies_ilmiah: '',
  zona_terumbu: '',
  kedalaman_meter: '',
  persentase_hidup: 85,
  status_kesehatan: 'Sehat',
  catatan_observasi: ''
})

const resetForm = () => {
  form.value = {
    kode_fragmen: '',
    spesies_ilmiah: '',
    zona_terumbu: '',
    kedalaman_meter: '',
    persentase_hidup: 85,
    status_kesehatan: 'Sehat',
    catatan_observasi: ''
  }
  validationErrors.value = {}
  editingId.value = null
  isEditMode.value = false
}

// Fetch Colonies
const fetchColonies = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await api.get('/coral-colonies')
    colonies.value = res.data.data || []
  } catch (err) {
    if (err.response && err.response.status === 401) {
      handleLogout()
    } else {
      errorMessage.value = 'Gagal mengambil data koloni karang dari server.'
    }
  } finally {
    loading.value = false
  }
}

// Open Create Modal
const openCreateModal = () => {
  resetForm()
  // Generate default fragment code suggestion
  const nextNum = colonies.value.length + 1
  form.value.kode_fragmen = `CRL-2026-${String(nextNum).padStart(3, '0')}`
  showModal.value = true
}

// Open Edit Modal
const openEditModal = (colony) => {
  resetForm()
  isEditMode.value = true
  editingId.value = colony.id
  form.value = {
    kode_fragmen: colony.kode_fragmen,
    spesies_ilmiah: colony.spesies_ilmiah,
    zona_terumbu: colony.zona_terumbu,
    kedalaman_meter: colony.kedalaman_meter,
    persentase_hidup: colony.persentase_hidup,
    status_kesehatan: colony.status_kesehatan,
    catatan_observasi: colony.catatan_observasi || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

// Submit Form (Create / Update)
const handleSubmit = async () => {
  actionLoading.value = true
  validationErrors.value = {}
  errorMessage.value = ''

  try {
    if (isEditMode.value) {
      await api.put(`/coral-colonies/${editingId.value}`, form.value)
      successMessage.value = `Koloni ${form.value.kode_fragmen} berhasil diperbarui!`
    } else {
      await api.post('/coral-colonies', form.value)
      successMessage.value = `Koloni baru ${form.value.kode_fragmen} berhasil ditambahkan!`
    }
    closeModal()
    await fetchColonies()
    setTimeout(() => { successMessage.value = '' }, 4000)
  } catch (err) {
    if (err.response && err.response.status === 422) {
      validationErrors.value = err.response.data.errors || {}
      errorMessage.value = 'Validasi gagal. Periksa kembali input formulir Anda.'
    } else {
      errorMessage.value = err.response?.data?.message || 'Terjadi kesalahan saat memproses data.'
    }
  } finally {
    actionLoading.value = false
  }
}

// Delete Colony
const handleDelete = async (colony) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus observasi fragmen ${colony.kode_fragmen} (${colony.spesies_ilmiah})?`)) {
    return
  }

  actionLoading.value = true
  try {
    await api.delete(`/coral-colonies/${colony.id}`)
    successMessage.value = `Fragmen ${colony.kode_fragmen} berhasil dihapus.`
    await fetchColonies()
    setTimeout(() => { successMessage.value = '' }, 4000)
  } catch (err) {
    errorMessage.value = 'Gagal menghapus data fragmen karang.'
  } finally {
    actionLoading.value = false
  }
}

// Logout
const handleLogout = async () => {
  try {
    await api.post('/logout')
  } catch (err) {
    console.error('Logout error:', err)
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    emit('logout')
  }
}

// Computed Filtered Colonies
const filteredColonies = computed(() => {
  if (!searchQuery.value) return colonies.value
  const q = searchQuery.value.toLowerCase()
  return colonies.value.filter(c =>
    c.kode_fragmen.toLowerCase().includes(q) ||
    c.spesies_ilmiah.toLowerCase().includes(q) ||
    c.zona_terumbu.toLowerCase().includes(q) ||
    c.status_kesehatan.toLowerCase().includes(q)
  )
})

// Statistics
const stats = computed(() => {
  const total = colonies.value.length
  const healthy = colonies.value.filter(c => c.status_kesehatan === 'Sehat').length
  const bleaching = colonies.value.filter(c => c.status_kesehatan.includes('Bleaching') || c.status_kesehatan.includes('Pemutihan')).length
  const recovering = colonies.value.filter(c => c.status_kesehatan === 'Pemulihan').length
  return { total, healthy, bleaching, recovering }
})

const getStatusBadgeClass = (status) => {
  if (status === 'Sehat') return 'badge-success'
  if (status.includes('Bleaching') || status.includes('Pemutihan')) return 'badge-danger'
  return 'badge-warning'
}

onMounted(() => {
  fetchColonies()
})
</script>

<template>
  <div class="dashboard-container">
    <!-- Navbar -->
    <header class="navbar">
      <div class="nav-brand">
        <span class="brand-icon">🪸</span>
        <div>
          <h1>Coral Reef Restoration Tracker</h1>
          <p class="nav-sub">Sistem Konservasi & Monitoring Terumbu Karang</p>
        </div>
      </div>
      <div class="nav-actions">
        <button @click="handleLogout" class="btn btn-outline-danger">
          Keluar (Logout)
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Global Alerts -->
      <div v-if="successMessage" class="alert alert-success">
        ✓ {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="alert alert-error">
        ⚠️ {{ errorMessage }}
      </div>

      <!-- Stats Cards -->
      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">📊</div>
          <div>
            <div class="stat-value">{{ stats.total }}</div>
            <div class="stat-label">Total Koloni Terdata</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-success">🌿</div>
          <div>
            <div class="stat-value">{{ stats.healthy }}</div>
            <div class="stat-label">Koloni Sehat</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-warning">🔄</div>
          <div>
            <div class="stat-value">{{ stats.recovering }}</div>
            <div class="stat-label">Fase Pemulihan</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon icon-danger">🌡️</div>
          <div>
            <div class="stat-value">{{ stats.bleaching }}</div>
            <div class="stat-label">Terdampak Pemutihan</div>
          </div>
        </div>
      </section>

      <!-- Action Toolbar -->
      <section class="toolbar">
        <div class="search-box">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari kode fragmen, spesies, zona..."
          />
        </div>
        <div class="toolbar-buttons">
          <button @click="fetchColonies" class="btn btn-secondary" :disabled="loading">
            ↻ Muat Ulang
          </button>
          <button @click="openCreateModal" class="btn btn-primary">
            + Tambah Fragmen Karang
          </button>
        </div>
      </section>

      <!-- Data Table -->
      <section class="table-card">
        <div v-if="loading" class="loading-state">
          Memuat data observasi terumbu karang...
        </div>
        <div v-else-if="filteredColonies.length === 0" class="empty-state">
          Belum ada data koloni terumbu karang yang sesuai kriteria pencarian.
        </div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Kode Fragmen</th>
                <th>Spesies Ilmiah</th>
                <th>Zona Terumbu</th>
                <th>Kedalaman</th>
                <th>Kesehatan (% Hidup)</th>
                <th>Status</th>
                <th>Catatan Observasi</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="colony in filteredColonies" :key="colony.id">
                <td>
                  <span class="code-badge">{{ colony.kode_fragmen }}</span>
                </td>
                <td class="species-cell">
                  <em>{{ colony.spesies_ilmiah }}</em>
                </td>
                <td>{{ colony.zona_terumbu }}</td>
                <td>{{ colony.kedalaman_meter }} m</td>
                <td>
                  <div class="progress-wrapper">
                    <div class="progress-bar-bg">
                      <div
                        class="progress-bar-fill"
                        :style="{
                          width: `${colony.persentase_hidup}%`,
                          backgroundColor: colony.persentase_hidup > 70 ? '#10b981' : (colony.persentase_hidup > 40 ? '#f59e0b' : '#ef4444')
                        }"
                      ></div>
                    </div>
                    <span class="progress-text">{{ colony.persentase_hidup }}%</span>
                  </div>
                </td>
                <td>
                  <span :class="['status-badge', getStatusBadgeClass(colony.status_kesehatan)]">
                    {{ colony.status_kesehatan }}
                  </span>
                </td>
                <td class="notes-cell">
                  {{ colony.catatan_observasi || '-' }}
                </td>
                <td class="actions-cell">
                  <button @click="openEditModal(colony)" class="btn-sm btn-edit" title="Ubah Observasi">
                    Edit
                  </button>
                  <button @click="handleDelete(colony)" class="btn-sm btn-delete" title="Hapus Data">
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <!-- Modal Form (Tambah / Edit) -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-dialog">
        <div class="modal-header">
          <h3>{{ isEditMode ? 'Edit Observasi Koloni Karang' : 'Tambah Koloni Karang Baru' }}</h3>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>

        <form @submit.prevent="handleSubmit" class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label>Kode Fragmen *</label>
              <input
                v-model="form.kode_fragmen"
                type="text"
                placeholder="Contoh: CRL-2026-001"
                required
              />
              <span v-if="validationErrors.kode_fragmen" class="input-error">
                {{ validationErrors.kode_fragmen[0] }}
              </span>
            </div>

            <div class="form-group">
              <label>Spesies Ilmiah *</label>
              <input
                v-model="form.spesies_ilmiah"
                type="text"
                placeholder="Contoh: Acropora cervicornis"
                required
              />
              <span v-if="validationErrors.spesies_ilmiah" class="input-error">
                {{ validationErrors.spesies_ilmiah[0] }}
              </span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Zona Terumbu *</label>
              <input
                v-model="form.zona_terumbu"
                type="text"
                placeholder="Contoh: Sektor Utara - Karimunjawa"
                required
              />
              <span v-if="validationErrors.zona_terumbu" class="input-error">
                {{ validationErrors.zona_terumbu[0] }}
              </span>
            </div>

            <div class="form-group">
              <label>Kedalaman (Meter) *</label>
              <input
                v-model.number="form.kedalaman_meter"
                type="number"
                step="0.1"
                min="0"
                placeholder="Contoh: 8.5"
                required
              />
              <span v-if="validationErrors.kedalaman_meter" class="input-error">
                {{ validationErrors.kedalaman_meter[0] }}
              </span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Persentase Jaringan Hidup (0 - 100%) *</label>
              <input
                v-model.number="form.persentase_hidup"
                type="number"
                min="0"
                max="100"
                required
              />
              <span v-if="validationErrors.persentase_hidup" class="input-error">
                {{ validationErrors.persentase_hidup[0] }}
              </span>
            </div>

            <div class="form-group">
              <label>Status Kesehatan *</label>
              <select v-model="form.status_kesehatan" required>
                <option value="Sehat">Sehat</option>
                <option value="Pemulihan">Pemulihan</option>
                <option value="Pemutihan/Bleaching">Pemutihan/Bleaching</option>
              </select>
              <span v-if="validationErrors.status_kesehatan" class="input-error">
                {{ validationErrors.status_kesehatan[0] }}
              </span>
            </div>
          </div>

          <div class="form-group">
            <label>Catatan Observasi</label>
            <textarea
              v-model="form.catatan_observasi"
              rows="3"
              placeholder="Catatan perkembangan koloni, pertumbuhan fragmen, atau kondisi lingkungan..."
            ></textarea>
            <span v-if="validationErrors.catatan_observasi" class="input-error">
              {{ validationErrors.catatan_observasi[0] }}
            </span>
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn btn-secondary">
              Batal
            </button>
            <button type="submit" class="btn btn-primary" :disabled="actionLoading">
              <span v-if="actionLoading">Menyimpan...</span>
              <span v-else>{{ isEditMode ? 'Simpan Perubahan' : 'Tambah Koloni' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  background-color: #f8fafc;
  display: flex;
  flex-direction: column;
}

.navbar {
  background: #ffffff;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.brand-icon {
  font-size: 2.2rem;
}

.nav-brand h1 {
  margin: 0;
  font-size: 1.3rem;
  color: #0f172a;
  font-weight: 700;
}

.nav-sub {
  margin: 0.15rem 0 0;
  font-size: 0.8rem;
  color: #64748b;
}

.main-content {
  flex: 1;
  max-width: 1280px;
  width: 100%;
  margin: 0 auto;
  padding: 1.75rem 2rem;
}

.alert {
  padding: 0.85rem 1.25rem;
  border-radius: 8px;
  margin-bottom: 1.25rem;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.alert-success {
  background-color: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.alert-error {
  background-color: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-bottom: 1.75rem;
}

.stat-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

.stat-icon {
  font-size: 1.8rem;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  border-radius: 10px;
}

.stat-value {
  font-size: 1.6rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.8rem;
  color: #64748b;
  margin-top: 0.2rem;
}

/* Toolbar */
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
}

.search-box input {
  padding: 0.65rem 1rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.9rem;
  width: 320px;
}

.search-box input:focus {
  outline: none;
  border-color: #0d9488;
}

.toolbar-buttons {
  display: flex;
  gap: 0.75rem;
}

/* Table Card */
.table-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  overflow: hidden;
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.9rem;
}

.data-table th {
  background: #f8fafc;
  padding: 0.9rem 1rem;
  font-weight: 600;
  color: #475569;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

.data-table td {
  padding: 0.9rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  color: #1e293b;
  vertical-align: middle;
}

.data-table tr:hover {
  background-color: #f8fafc;
}

.code-badge {
  background: #f1f5f9;
  color: #0f172a;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-family: monospace;
  font-size: 0.85rem;
  font-weight: 600;
  border: 1px solid #e2e8f0;
}

.species-cell {
  font-weight: 500;
  color: #0f766e;
}

.progress-wrapper {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.progress-bar-bg {
  width: 90px;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  transition: width 0.3s;
}

.progress-text {
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.65rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-success {
  background: #dcfce7;
  color: #166534;
}

.badge-warning {
  background: #fef3c7;
  color: #92400e;
}

.badge-danger {
  background: #fee2e2;
  color: #991b1b;
}

.notes-cell {
  max-width: 250px;
  font-size: 0.85rem;
  color: #64748b;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.actions-cell {
  white-space: nowrap;
}

.btn-sm {
  padding: 0.35rem 0.65rem;
  font-size: 0.8rem;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid transparent;
  margin-right: 0.35rem;
  font-weight: 500;
  transition: all 0.15s;
}

.btn-edit {
  background: #f0fdfa;
  color: #0d9488;
  border-color: #ccfbf1;
}

.btn-edit:hover {
  background: #ccfbf1;
}

.btn-delete {
  background: #fef2f2;
  color: #ef4444;
  border-color: #fee2e2;
}

.btn-delete:hover {
  background: #fee2e2;
}

/* General Buttons */
.btn {
  padding: 0.6rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background: #0d9488;
  color: #ffffff;
}

.btn-primary:hover:not(:disabled) {
  background: #0f766e;
}

.btn-secondary {
  background: #ffffff;
  color: #475569;
  border: 1px solid #cbd5e1;
}

.btn-secondary:hover:not(:disabled) {
  background: #f8fafc;
}

.btn-outline-danger {
  background: transparent;
  color: #ef4444;
  border: 1px solid #fca5a5;
}

.btn-outline-danger:hover {
  background: #fef2f2;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 3rem 1.5rem;
  color: #64748b;
  font-size: 0.95rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 1.5rem;
}

.modal-dialog {
  background: #ffffff;
  border-radius: 14px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.15rem;
  color: #0f172a;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #94a3b8;
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
}

.form-group input, .form-group select, .form-group textarea {
  padding: 0.65rem 0.8rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.875rem;
  font-family: inherit;
}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
  outline: none;
  border-color: #0d9488;
}

.input-error {
  color: #ef4444;
  font-size: 0.75rem;
}

.modal-footer {
  margin-top: 1rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}
</style>
