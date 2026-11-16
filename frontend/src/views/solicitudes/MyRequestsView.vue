<script setup>
import { ref, onMounted } from 'vue'
import DataGrid from '@/components/DataGrid.vue'
import RequestStatusBadge from '@/components/RequestStatusBadge.vue'
import { listarMisSolicitudes, cancelarSolicitud, obtenerHistorial } from '@/services/requestsApi'

const solicitudes = ref([])
const cargando = ref(true)
const errorMsg = ref('')

const columns = [
  { key: 'id', label: 'Folio', searchable: false },
  { key: 'servicio', label: 'Servicio' },
  { key: 'precio_contratado', label: 'Precio', formatter: (val) => `$${val}` },
  { key: 'fecha_solicitud', label: 'Fecha Solicitud' },
  { key: 'fecha_programada', label: 'Programación', formatter: (val) => val || 'Sin agendar' },
  { key: 'estado', label: 'Estado' },
  { key: 'actions', label: 'Acciones', searchable: false, export: false }
]

async function cargarSolicitudes() {
  try {
    cargando.value = true
    solicitudes.value = await listarMisSolicitudes()
  } catch (error) {
    errorMsg.value = error.message || 'Error al cargar las solicitudes'
  } finally {
    cargando.value = false
  }
}

async function manejarCancelar(id) {
  if (!confirm('¿Estás seguro de que deseas cancelar esta solicitud?')) return
  try {
    await cancelarSolicitud(id, 'Cancelada por el cliente')
    alert('Solicitud cancelada exitosamente')
    cargarSolicitudes() 
  } catch (error) {
    alert(error.message || 'No se pudo cancelar la solicitud')
  }
}

const modalHistorial = ref(false)
const historial = ref([])
const cargandoHistorial = ref(false)

async function verHistorial(id) {
  modalHistorial.value = true
  cargandoHistorial.value = true
  historial.value = []
  try {
    historial.value = await obtenerHistorial(id)
  } catch (error) {
    alert('Error al cargar el historial')
    modalHistorial.value = false
  } finally {
    cargandoHistorial.value = false
  }
}

onMounted(() => {
  cargarSolicitudes()
})
</script>

<template>
  <div class="my-requests-view" style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
    <h1>Mis Solicitudes</h1>
    <p v-if="errorMsg" class="error" style="color: red;">{{ errorMsg }}</p>
    <p v-if="cargando">Cargando solicitudes...</p>

    <DataGrid v-else :rows="solicitudes" :columns="columns" title="Mis Solicitudes" fileName="mis-solicitudes">
      <template #cell-estado="{ value }">
        <RequestStatusBadge :status="value" />
      </template>

      <template #actions="{ row }">
        <div style="display: flex; gap: 0.5rem;">
          <button class="btn btn-info" style="background: #06b6d4; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="verHistorial(row.id)">
            Historial
          </button>
          <button 
            v-if="['SOLICITADA', 'AUTORIZADA'].includes(row.estado)" 
            class="btn btn-danger" 
            style="background: #ef4444; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;"
            @click="manejarCancelar(row.id)"
          >
            Cancelar
          </button>
        </div>
      </template>
    </DataGrid>

    <div v-if="modalHistorial" class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;" @click.self="modalHistorial = false">
      <div class="modal-content" style="background: white; padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <h2>Historial de la Solicitud</h2>
        <div v-if="cargandoHistorial">Cargando historial...</div>
        <div v-else class="timeline" style="border-left: 2px solid #ccc; margin: 1rem 0; padding-left: 1rem;">
          <div v-for="item in historial" :key="item.id" class="timeline-item" style="margin-bottom: 1rem;">
            <p><strong>{{ item.fecha }}</strong> - {{ item.usuario }}</p>
            <p>Cambió de <span class="badge">{{ item.estado_anterior || 'Ninguno' }}</span> a <span class="badge">{{ item.estado_nuevo }}</span></p>
            <p v-if="item.comentario" class="comentario" style="font-style: italic; color: #555;">"{{ item.comentario }}"</p>
          </div>
        </div>
        <button class="btn btn-secondary" style="background: #6b7280; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="modalHistorial = false">Cerrar</button>
      </div>
    </div>
  </div>
</template>