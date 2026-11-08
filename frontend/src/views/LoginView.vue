<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../services/auth'

const router = useRouter()
const route = useRoute()
const { login } = useAuth()

const usuario = ref('')
const contrasena = ref('')
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    await login({ usuario: usuario.value, contrasena: contrasena.value })
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
    <div class="login-container">
      <form class="login-form" @submit.prevent="handleLogin">
        <div class="form-header">
          <div class="login-icon">🔒</div>
          <h2>Iniciar Sesión</h2>
          <p>Ingresa tus credenciales para acceder.</p>
        </div>

        <div class="field">
          <label for="usuario">Usuario</label>
          <input id="usuario" v-model="usuario" type="text" placeholder="Ej. admin" required>
        </div>

        <div class="field">
          <label for="contrasena">Contraseña</label>
          <input id="contrasena" v-model="contrasena" type="password" placeholder="Ingresa tu contraseña" required>
        </div>

        <p v-if="error" class="form-message error">{{ error }}</p>

        <button class="btn btn-primary" type="submit" :disabled="loading">
          {{ loading ? 'Validando...' : 'Iniciar sesión' }}
        </button>
      </form>
    </div>
  </section>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  background: linear-gradient(135deg, #312e81, #4f46e5, #0891b2);
}
.login-form {
  width: min(400px, 90vw);
  background: white;
  padding: 2.5rem;
  border-radius: 1.5rem;
  box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
}
.form-header { text-align: center; margin-bottom: 2rem; }
.login-icon { font-size: 3rem; }
.field { margin-bottom: 1.25rem; }
.field label { display: block; font-weight: 700; margin-bottom: 0.5rem; }
.field input { width: 100%; padding: 0.9rem 1rem; border: 1px solid #cbd5e1; border-radius: 0.7rem; }
.btn { width: 100%; border: 0; padding: 0.9rem; border-radius: 0.7rem; font-weight: 700; cursor: pointer; }
.btn-primary { background: #4f46e5; color: white; }
.form-message.error { background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center; }
</style>