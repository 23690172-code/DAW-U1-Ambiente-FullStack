<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '@/services/auth'

const router = useRouter()
const { user, logout } = useAuth()

async function cerrarSession() {
  await logout()
  router.push('/login')
}
</script>

<template>
  <header class="app-header">
    <div class="header-brand">
      <div class="brand-icon">V</div>
      <div>
        <strong>Aplicación Full Stack</strong>
        <small>Vue + PHP</small>
      </div>
    </div>

    <nav class="nav-links" aria-label="Navegación principal">
      <RouterLink to="/dashboard">Dashboard</RouterLink>
      <RouterLink to="/servicios">Servicios</RouterLink>
    </nav>

    <div class="header-user">
      <span>{{ user?.nombre }}</span>
      <button class="btn-logout" @click="cerrarSession">
        Cerrar sesión
      </button>
    </div>
  </header>
</template>