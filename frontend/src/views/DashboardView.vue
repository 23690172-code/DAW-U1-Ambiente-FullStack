<script setup>
import { useAuth } from '../services/auth'
const { user } = useAuth()
</script>

<template>
  <section class="dashboard">
    <div class="dashboard-hero">
      <div>
        <span class="badge">Panel principal</span>
        <h1>Bienvenido, {{ user?.nombre }}</h1>
        <p>Desde este espacio puedes consultar los módulos disponibles de la aplicación.</p>
      </div>
    </div>

    <div class="dashboard-grid">
      <article class="dashboard-card card-blue">
        <h2>Servicios</h2>
        <p>Consulta los servicios disponibles mediante la API.</p>
        <RouterLink to="/servicios" class="card-link">Consultar servicios →</RouterLink>
      </article>

      <article class="dashboard-card card-green">
        <h2>Usuario</h2>
        <p>{{ user?.usuario }}</p>
        <strong>{{ user?.rol }}</strong>
      </article>

      <article class="dashboard-card card-orange">
        <h2>Sesión</h2>
        <p>Acceso autenticado mediante el backend PHP.</p>
      </article>
    </div>
  </section>
</template>

<style scoped>
.dashboard { display: grid; gap: 2rem; }
.dashboard-hero {
  padding: 2.5rem; border-radius: 1.5rem; color: white;
  background: linear-gradient(135deg, #4f46e5, #0891b2);
}
.badge { background: rgba(255,255,255,0.2); padding: 0.5rem 0.8rem; border-radius: 999px; }
.dashboard-hero h1 { font-size: 2.5rem; margin: 1rem 0 0.5rem; }
.dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
.dashboard-card { padding: 1.8rem; border-radius: 1.2rem; color: white; box-shadow: 0 15px 30px rgba(15,23,42,0.12); }
.card-blue { background: linear-gradient(135deg, #2563eb, #4f46e5); }
.card-green { background: linear-gradient(135deg, #059669, #0891b2); }
.card-orange { background: linear-gradient(135deg, #ea580c, #db2777); }
.card-link { display: inline-block; margin-top: 1rem; color: white; font-weight: 700; text-decoration: none; }
</style>