<script setup>
import { ref, onMounted } from 'vue'
import DataGrid from '@/components/DataGrid.vue'
import RequestStatusBadge from '@/components/RequestStatusBadge.vue'
import RequestActionModal from '@/components/RequestActionModal.vue'
import { 
  listarSolicitudes, autorizarSolicitud, rechazarSolicitud, 
  programarSolicitud, iniciarSolicitud, completarSolicitud, cancelarSolicitud,
  obtenerHistorial
} from '@/services/requestsApi'

const solicitudes = ref([])
const cargando = ref(true)
const errorMsg = ref('')

const columns = [
  { key: 'id', label: 'Folio', searchable: false },
  { key: 'cliente', label: 'Cliente' },
  { key: 'servicio', label: 'Servicio' },
  { key: 'fecha_solicitud', label: 'Fecha Sol.' },
  { key: 'estado', label: 'Estado' },
  { key: 'actions', label: 'Acciones', searchable: false, export: false }
]

async function cargarSolicitudes() {
  try {
    cargando.value = true
    solicitudes.value = await listarSolicitudes()
  } catch (error) {
    errorMsg.value = error.message || 'Error al cargar las solicitudes'
  } finally {
    cargando.value = false
  }
}

const modalAccion = ref(false)
const accionTipo = ref('')
const solicitudSeleccionada = ref(null)

function abrirModal(tipo, solicitud) {
  accionTipo.value = tipo
  solicitudSeleccionada.value = solicitud
  modalAccion.value = true
}

async function procesar(payload) {
  const { type, id, observaciones, fecha_programada } = payload
  try {
    if (type === 'AUTHORIZE') await autorizarSolicitud(id)
    else if (type === 'REJECT') await rechazarSolicitud(id, observaciones)
    else if (type === 'SCHEDULE') await programarSolicitud(id, { fecha_programada, observaciones })
    else if (type === 'START') await iniciarSolicitud(id)
    else if (type === 'COMPLETE') await completarSolicitud(id, observaciones)
    else if (type === 'CANCEL') await cancelarSolicitud(id, observaciones)

    modalAccion.value = false
    alert('Acción realizada correctamente')
    cargarSolicitudes()
  } catch (error) {
    alert(error.message || 'Error al procesar la acción')
  }
}

const modalHistorial = ref(false)
const historial = ref([])

async function verHistorial(id) {
  modalHistorial.value = true
  historial.value = []
  try {
    historial.value = await obtenerHistorial(id)
  } catch (error) {
    alert('Error al cargar el historial')
    modalHistorial.value = false
  }
}

onMounted(() => {
  cargarSolicitudes()
})
</script>

<template>
  <div class="admin-requests-view" style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
    <h1>Gestión de Solicitudes</h1>
    <p v-if="errorMsg" class="error" style="color: red;">{{ errorMsg }}</p>
    <p v-if="cargando">Cargando solicitudes...</p>

    <DataGrid v-else :rows="solicitudes" :columns="columns" title="Todas las Solicitudes" fileName="reporte-solicitudes">
      <template #cell-estado="{ value }">
        <RequestStatusBadge :status="value" />
      </template>

      <template #actions="{ row }">
        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
          <template v-if="row.estado === 'SOLICITADA'">
            <button style="background: #10b981; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="abrirModal('AUTHORIZE', row)">Autorizar</button>
            <button style="background: #ef4444; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="abrirModal('REJECT', row)">Rechazar</button>
          </template>
          <template v-else-if="row.estado === 'AUTORIZADA'">
            <button style="background: #3b82f6; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="abrirModal('SCHEDULE', row)">Programar</button>
            <button style="background: #ef4444; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="abrirModal('CANCEL', row)">Cancelar</button>
          </template>
          <template v-else-if="row.estado === 'PROGRAMADA'">
            <button style="background: #f59e0b; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="abrirModal('START', row)">Iniciar</button>
            <button style="background: #ef4444; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="abrirModal('CANCEL', row)">Cancelar</button>
          </template>
          <template v-else-if="row.estado === 'EN_PROCESO'">
            <button style="background: #10b981; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="abrirModal('COMPLETE', row)">Completar</button>
          </template>
          <template v-else>
            <button style="background: #06b6d4; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="verHistorial(row.id)">Historial</button>
          </template>
        </div>
      </template>
    </DataGrid>

    <RequestActionModal v-if="modalAccion" :type="accionTipo" :request="solicitudSeleccionada" @confirm="procesar" @cancel="modalAccion = false" />

    <div v-if="modalHistorial" class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;" @click.self="modalHistorial = false">
      <div class="modal-content" style="background: white; padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <h2>Historial de la Solicitud</h2>
        <div class="timeline" style="border-left: 2px solid #ccc; margin: 1rem 0; padding-left: 1rem;">
          <div v-for="item in historial" :key="item.id" class="timeline-item" style="margin-bottom: 1rem;">
            <p><strong>{{ item.fecha }}</strong> - {{ item.usuario }}</p>
            <p>Cambió de <span class="badge">{{ item.estado_anterior || 'Ninguno' }}</span> a <span class="badge">{{ item.estado_nuevo }}</span></p>
            <p v-if="item.comentario" style="font-style: italic; color: #555;">"{{ item.comentario }}"</p>
          </div>
        </div>
        <button style="background: #6b7280; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;" @click="modalHistorial = false">Cerrar</button>
      </div>
    </div>
  </div>
</template>