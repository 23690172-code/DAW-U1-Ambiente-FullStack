<script setup>
defineProps({
  servicio: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['seleccionar'])
</script>

<template>
  <article class="service-card">
    <h3>{{ servicio.nombre }}</h3>
    <p>{{ servicio.descripcion }}</p>
    <p class="price">${{ servicio.precio }}</p>
    <button
      type="button"
      :aria-label="`Ver información de ${servicio.nombre}`"
      @click="emit('seleccionar', servicio)"
    >
      Ver información
    </button>
  </article>
</template>