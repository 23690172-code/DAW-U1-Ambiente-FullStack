<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  type: { type: String, required: true }, 
  request: { type: Object, required: true } 
})

const emit = defineEmits(['confirm', 'cancel'])

const options = {
  AUTHORIZE: { title: 'Autorizar solicitud', button: 'Autorizar' },
  REJECT: { title: 'Rechazar solicitud', button: 'Rechazar', comment: true },
  SCHEDULE: { title: 'Programar servicio', button: 'Programar', date: true, comment: true },
  START: { title: 'Iniciar servicio', button: 'Iniciar' },
  COMPLETE: { title: 'Completar servicio', button: 'Completar', comment: true },
  CANCEL: { title: 'Cancelar solicitud', button: 'Cancelar', comment: true }
}

const config = computed(() => options[props.type] || {})
const observaciones = ref('')
const fechaProgramada = ref('')

watch(() => props.type, () => {
  observaciones.value = ''
  fechaProgramada.value = ''
})

function confirmar() {
  if (config.value.comment && observaciones.value.trim() === '') {
    alert('Por favor, ingrese un comentario/motivo.')
    return
  }
  if (config.value.date && fechaProgramada.value === '') {
    alert('Por favor, seleccione una fecha de programación.')
    return
  }

  emit('confirm', {
    type: props.type,
    id: props.request.id,
    observaciones: observaciones.value,
    fecha_programada: fechaProgramada.value
  })
}
</script>

<template>
  <div class="modal-overlay" @click.self="emit('cancel')">
    <div class="modal-content">
      <h2>{{ config.title }}</h2>
      <p class="request-info">Solicitud <strong>#{{ request.id }}</strong> - {{ request.servicio }}</p>

      <div v-if="config.date" class="form-group">
        <label>Fecha y hora de programación:</label>
        <input type="datetime-local" v-model="fechaProgramada" class="form-control" />
      </div>

      <div v-if="config.comment" class="form-group">
        <label>Observaciones / Motivo:</label>
        <textarea v-model="observaciones" rows="3" class="form-control" placeholder="Escriba aquí..."></textarea>
      </div>

      <div class="modal-actions">
        <button class="btn btn-secondary" @click="emit('cancel')">Cancelar</button>
        <button class="btn btn-primary" @click="confirmar">{{ config.button }}</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; padding: 2rem; border-radius: 8px; width: 100%; max-width: 500px; }
.request-info { background: #f3f4f6; padding: 0.5rem; border-radius: 4px; margin-bottom: 1rem; }
.form-group { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
.form-control { padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; }
.modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
</style>