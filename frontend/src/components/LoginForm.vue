<script setup>
import { ref } from 'vue'
import api from '../api/axios'

const emit = defineEmits(['login-success'])

const email = ref('admin@coraltracker.id')
const password = ref('password123')
const loading = ref(false)
const errorMessage = ref('')

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value
    })

    if (response.data && response.data.token) {
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user || {}))
      emit('login-success', response.data.user)
    }
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Gagal terhubung ke server. Pastikan API backend berjalan.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <div class="logo-icon">🪸</div>
        <h2>Coral Reef Tracker</h2>
        <p class="subtitle">Sistem Manajemen Konservasi & Restorasi Terumbu Karang</p>
      </div>

      <div v-if="errorMessage" class="alert alert-error">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Email Peneliti</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="admin@coraltracker.id"
            required
            autocomplete="email"
          />
        </div>

        <div class="form-group">
          <label for="password">Kata Sandi</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="••••••••"
            required
            autocomplete="current-password"
          />
        </div>

        <button type="submit" class="btn btn-primary" :disabled="loading">
          <span v-if="loading">Memverifikasi...</span>
          <span v-else>Masuk ke Dashboard</span>
        </button>
      </form>

      <div class="demo-credentials">
        <p><strong>Akun Uji Default:</strong></p>
        <code>admin@coraltracker.id</code> / <code>password123</code>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 80vh;
  padding: 1.5rem;
}

.login-card {
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border-radius: 14px;
  padding: 2.25rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

.login-header {
  text-align: center;
  margin-bottom: 1.75rem;
}

.logo-icon {
  font-size: 2.75rem;
  margin-bottom: 0.5rem;
}

.login-header h2 {
  margin: 0;
  font-size: 1.6rem;
  color: #0f172a;
  font-weight: 700;
}

.subtitle {
  color: #64748b;
  font-size: 0.875rem;
  margin-top: 0.4rem;
}

.alert-error {
  background-color: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.25rem;
  font-size: 0.875rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  text-align: left;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
}

.form-group input {
  padding: 0.75rem 0.9rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #0d9488;
  box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
}

.btn {
  padding: 0.8rem 1.2rem;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background-color: #0d9488;
  color: #ffffff;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0f766e;
}

.btn-primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.demo-credentials {
  margin-top: 1.75rem;
  padding: 0.75rem;
  background-color: #f8fafc;
  border-radius: 8px;
  border: 1px dashed #cbd5e1;
  font-size: 0.8rem;
  color: #475569;
  text-align: center;
}

.demo-credentials code {
  background: #e2e8f0;
  padding: 0.15rem 0.35rem;
  border-radius: 4px;
  color: #0f172a;
  font-family: monospace;
}
</style>
