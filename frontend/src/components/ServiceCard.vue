<script setup>
const props = defineProps({
  servicio: { type: Object, required: true },
  canRequest: { type: Boolean, default: false }
})

const emit = defineEmits(['request'])
</script>

<template>
  <div class="service-card">
    <div class="card-header">
      <div class="icon-box">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
      </div>
      <span class="status-tag" :class="servicio.activo ? 'active' : 'inactive'">
        {{ servicio.activo ? 'Disponible' : 'No disponible' }}
      </span>
    </div>

    <div class="card-body">
      <h3>{{ servicio.nombre }}</h3>
      <p class="description">{{ servicio.descripcion }}</p>
    </div>

    <div class="card-footer">
      <div class="info-row">
        <span class="label">Precio:</span>
        <span class="value">${{ servicio.precio }}</span>
      </div>
      <div class="info-row">
        <span class="label">Duración:</span>
        <span class="value">{{ servicio.duracion_minutos }} min</span>
      </div>

      <button 
        v-if="canRequest" 
        type="button" 
        class="btn-request" 
        @click="emit('request', servicio)"
      >
        Solicitar servicio
      </button>
      
      <button 
        v-else 
        type="button" 
        class="btn-request disabled" 
        disabled
      >
        No disponible para tu rol
      </button>
    </div>
  </div>
</template>

<style scoped>
.service-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s, box-shadow 0.2s;
}

.service-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.icon-box {
  width: 45px;
  height: 45px;
  background: #eff6ff;
  color: #2563eb;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-tag {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  text-transform: uppercase;
}

.status-tag.active {
  background: #dcfce7;
  color: #166534;
}

.status-tag.inactive {
  background: #fee2e2;
  color: #991b1b;
}

.card-body {
  flex: 1;
}

.card-body h3 {
  margin: 0 0 0.5rem 0;
  color: #1e293b;
  font-size: 1.25rem;
}

.description {
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.5;
  margin-bottom: 1.5rem;
}

.card-footer {
  border-top: 1px solid #f1f5f9;
  padding-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
}

.label {
  color: #64748b;
}

.value {
  font-weight: 600;
  color: #1e293b;
}

.btn-request {
  margin-top: 1rem;
  width: 100%;
  background: #4f46e5;
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-request:hover:not(.disabled) {
  background: #4338ca;
}

.btn-request.disabled {
  background: #e2e8f0;
  color: #94a3b8;
  cursor: not-allowed;
}
</style>