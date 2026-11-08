<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '../services/auth'

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
      
      <RouterLink v-if="user?.rol === 'ADMIN_SISTEMA'" to="/usuarios">
        Usuarios
      </RouterLink>
      <RouterLink v-if="user?.rol === 'ADMIN_SISTEMA' || user?.rol === 'ADMIN_SERVICIOS'" to="/servicios/admin">
        Administrar servicios
      </RouterLink>
    </nav>
    
    <div class="header-user">
      <span>{{ user?.nombre }}</span>
      <button class="btn-logout" @click="cerrarSession">Salir</button>
    </div>
  </header>
</template>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: white;
  border-bottom: 1px solid #e2e8f0;
}
.header-brand { display: flex; align-items: center; gap: 1rem; }
.brand-icon {
  width: 40px; height: 40px;
  background: #4f46e5; color: white;
  display: grid; place-items: center;
  border-radius: 10px; font-weight: bold; font-size: 1.2rem;
}
.brand-icon + div { display: flex; flex-direction: column; }
.brand-icon + div small { color: #64748b; font-size: 0.8rem; }
.nav-links { display: flex; gap: 1.5rem; }
.nav-links a { text-decoration: none; color: #334155; font-weight: 600; }
.nav-links a:hover { color: #4f46e5; }
.header-user { display: flex; align-items: center; gap: 1rem; }
.btn-logout {
  background: #fee2e2; color: #991b1b;
  border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: bold;
}
</style>