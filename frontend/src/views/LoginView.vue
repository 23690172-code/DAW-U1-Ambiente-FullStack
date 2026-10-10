<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import LoginForm from '@/components/LoginForm.vue'
import { useAuth } from '@/services/auth'

const router = useRouter()
const route = useRoute()
const { login } = useAuth()

const loading = ref(false)
const error = ref('')

async function handleLogin(credentials) {
  loading.value = true
  error.value = ''

  try {
    await login(credentials)
    const destino = route.query.redirect || '/dashboard'
    router.push(destino)
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="login-page">
    <div class="login-presentation">
      <span class="presentation-tag">Desarrollo de Aplicaciones Web</span>
      <h2>Aplicación Full Stack</h2>
      <p>
        Interfaz desarrollada con Vue, conectada a un backend PHP mediante una API.
      </p>
      <div class="presentation-features">
        <span>✓ Vue 3</span>
        <span>✓ Docker</span>
        <span>✓ PHP</span>
        <span>✓ API</span>
      </div>
    </div>

    <div class="login-container">
      <LoginForm :loading="loading" :error="error" @submit="handleLogin" />
    </div>
  </section>
</template>