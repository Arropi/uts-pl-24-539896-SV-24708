<script setup>
import { ref, onMounted } from 'vue'
import LoginForm from './components/LoginForm.vue'
import CoralDashboard from './components/CoralDashboard.vue'

const isAuthenticated = ref(false)
const currentUser = ref(null)

const checkAuth = () => {
  const token = localStorage.getItem('token')
  const user = localStorage.getItem('user')
  if (token) {
    isAuthenticated.value = true
    currentUser.value = user ? JSON.parse(user) : null
  } else {
    isAuthenticated.value = false
    currentUser.value = null
  }
}

const onLoginSuccess = (user) => {
  isAuthenticated.value = true
  currentUser.value = user
}

const onLogout = () => {
  isAuthenticated.value = false
  currentUser.value = null
}

onMounted(() => {
  checkAuth()
})
</script>

<template>
  <CoralDashboard
    v-if="isAuthenticated"
    :user="currentUser"
    @logout="onLogout"
  />
  <LoginForm
    v-else
    @login-success="onLoginSuccess"
  />
</template>

<style>
/* Global resets & typography */
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #1e293b;
  background-color: #f8fafc;
  -webkit-font-smoothing: antialiased;
}
</style>
