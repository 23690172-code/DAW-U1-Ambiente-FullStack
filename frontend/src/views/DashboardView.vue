<script setup>
import { useAuth } from '@/services/auth'
const { user } = useAuth()
</script>

<template>
  <section class="dashboard">
    <div class="dashboard-hero">
      <div>
        <span class="badge">Panel principal</span>
        <h1>Bienvenido, {{ user?.nombre }}</h1>
        <p>
          Desde este espacio puedes consultar los módulos disponibles de la aplicación.
        </p>
        <div class="hero-icon"></div>
      </div>
    </div>

    <div class="dashboard-grid">
      <article class="dashboard-card card-blue">
        <span class="card-icon"></span>
        <h2>Servicios</h2>
        <p>Consulta los servicios disponibles mediante la API.</p>
        <RouterLink to="/servicios" class="card-link">
          Consultar servicios
        </RouterLink>
      </article>

      <article class="dashboard-card card-green">
        <span class="card-icon"></span>
        <h2>Usuario</h2>
        <p>{{ user?.usuario }}</p>
        <strong>{{ user?.rol }}</strong>
      </article>

      <article class="dashboard-card card-orange">
        <span class="card-icon"></span>
        <h2>Sesión</h2>
        <p>Acceso autenticado mediante el backend PHP.</p>
      </article>
    </div>
  </section>
</template>