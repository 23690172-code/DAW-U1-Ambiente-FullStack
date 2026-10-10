<script setup>
defineProps({
  servicio: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <article class="service-card">
    <h3>{{ servicio.nombre }}</h3>
    <p>{{ servicio.descripcion }}</p>
    <p class="price">${{ servicio.precio }}</p>
    <button type="button" :aria-label="`Consultar ${servicio.nombre}`">
      Consultar
    </button>
  </article>
</template>