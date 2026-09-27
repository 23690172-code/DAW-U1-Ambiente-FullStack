<template>
  <section class="home">
    <h2>Inicio</h2>
    <p>Bienvenido a la aplicación web desarrollada con Vue.</p>
    <p>Desde esta plataforma puedes consultar los servicios disponibles y visualizar su información.</p>
    <RouterLink to="/servicios" class="button">Ver servicios</RouterLink>
  </section>
</template>

<style scoped>
.home {
  padding: 2rem 0;
}
.home h2 {
  margin-bottom: 1rem;
}
.home p {
  margin-bottom: 1rem;
}
.button {
  display: inline-block;
  padding: 0.75rem 1.25rem;
  text-decoration: none;
  border: 1px solid #333;
  border-radius: 0.5rem;
}
</style>