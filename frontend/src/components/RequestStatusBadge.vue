<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true }
})

const labels = {
  SOLICITADA: 'Solicitada',
  AUTORIZADA: 'Autorizada',
  RECHAZADA: 'Rechazada',
  PROGRAMADA: 'Programada',
  EN_PROCESO: 'En proceso',
  COMPLETADA: 'Completada',
  CANCELADA: 'Cancelada'
}

const label = computed(() => labels[props.status] || props.status)
</script>

<template>
  <span class="status-badge" :class="`status-${status.toLowerCase()}`">
    {{ label }}
  </span>
</template>