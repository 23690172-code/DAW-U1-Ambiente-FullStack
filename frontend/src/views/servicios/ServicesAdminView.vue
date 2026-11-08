<script setup>
import { onMounted, reactive, ref } from 'vue'
import DataGrid from '../../components/DataGrid.vue'
import { listarServicios, crearServicio, actualizarServicio, cambiarEstadoServicio } from '../../services/servicesApi'

/*
|--------------------------------------------------------------------------
| Variables principales
|--------------------------------------------------------------------------
*/
const servicios = ref([])
const modal = ref(false)
const editando = ref(null)

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const mensaje = ref('')

/*
|--------------------------------------------------------------------------
| Formulario
|--------------------------------------------------------------------------
*/
const form = reactive({
  nombre: '',
  descripcion: '',
  precio: '',
  duracion_minutos: '',
  activo: 1
})

/*
|--------------------------------------------------------------------------
| Columnas del DataGrid
|--------------------------------------------------------------------------
*/
const columns = [
  {
    key: 'nombre',
    label: 'Servicio'
  },
  {
    key: 'descripcion',
    label: 'Descripción'
  },
  {
    key: 'precio',
    label: 'Precio',
    formatter: value => {
      const numero = Number(value)
      if (Number.isNaN(numero)) {
        return '$0.00'
      }
      return numero.toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN'
      })
    }
  },
  {
    key: 'duracion_minutos',
    label: 'Duración',
    formatter: value => `${value} min`
  },
  {
    key: 'activo',
    label: 'Estado',
    formatter: value => Number(value) === 1 ? 'Activo' : 'Inactivo'
  },
  {
    key: 'actions',
    label: 'Acciones',
    searchable: false,
    export: false
  }
]

/*
|--------------------------------------------------------------------------
| Cargar servicios
|--------------------------------------------------------------------------
*/
async function cargar() {
  loading.value = true
  error.value = ''
  try {
    servicios.value = await listarServicios()
  } catch (e) {
    error.value = e.message || 'No fue posible consultar los servicios'
  } finally {
    loading.value = false
  }
}

/*
|--------------------------------------------------------------------------
| Limpiar formulario
|--------------------------------------------------------------------------
*/
function limpiarFormulario() {
  Object.assign(form, {
    nombre: '',
    descripcion: '',
    precio: '',
    duracion_minutos: '',
    activo: 1
  })
}

/*
|--------------------------------------------------------------------------
| Nuevo servicio
|--------------------------------------------------------------------------
*/
function nuevo() {
  editando.value = null
  error.value = ''
  mensaje.value = ''
  limpiarFormulario()
  modal.value = true
}

/*
|--------------------------------------------------------------------------
| Editar servicio
|--------------------------------------------------------------------------
*/
function editar(servicio) {
  editando.value = servicio.id
  error.value = ''
  mensaje.value = ''

  Object.assign(form, {
    nombre: servicio.nombre,
    descripcion: servicio.descripcion,
    precio: Number(servicio.precio),
    duracion_minutos: Number(servicio.duracion_minutos),
    activo: Number(servicio.activo)
  })

  modal.value = true
}

/*
|--------------------------------------------------------------------------
| Cerrar modal
|--------------------------------------------------------------------------
*/
function cerrarModal() {
  modal.value = false
  editando.value = null
  error.value = ''
  limpiarFormulario()
}

/*
|--------------------------------------------------------------------------
| Validación básica frontend
|--------------------------------------------------------------------------
*/
function validarFormulario() {
  if (form.nombre.trim() === '') {
    error.value = 'El nombre del servicio es obligatorio'
    return false
  }

  if (form.descripcion.trim() === '') {
    error.value = 'La descripción es obligatoria'
    return false
  }

  if (form.precio === '' || Number.isNaN(Number(form.precio)) || Number(form.precio) < 0) {
    error.value = 'El precio debe ser un número mayor o igual a cero'
    return false
  }

  if (form.duracion_minutos === '' || !Number.isInteger(Number(form.duracion_minutos)) || Number(form.duracion_minutos) <= 0) {
    error.value = 'La duración debe ser un número entero mayor que cero'
    return false
  }

  return true
}

/*
|--------------------------------------------------------------------------
| Guardar servicio
|--------------------------------------------------------------------------
*/
async function guardar() {
  error.value = ''
  mensaje.value = ''

  if (!validarFormulario()) {
    return
  }

  saving.value = true

  const data = {
    nombre: form.nombre.trim(),
    descripcion: form.descripcion.trim(),
    precio: Number(form.precio),
    duracion_minutos: Number(form.duracion_minutos),
    activo: Number(form.activo)
  }

  try {
    if (editando.value) {
      await actualizarServicio(editando.value, data)
      mensaje.value = 'Servicio actualizado correctamente'
    } else {
      await crearServicio(data)
      mensaje.value = 'Servicio registrado correctamente'
    }

    modal.value = false
    editando.value = null
    limpiarFormulario()

    await cargar()
  } catch (e) {
    error.value = e.message || 'No fue posible guardar el servicio'
  } finally {
    saving.value = false
  }
}

/*
|--------------------------------------------------------------------------
| Cambiar estado
|--------------------------------------------------------------------------
*/
async function cambiarEstado(servicio) {
  error.value = ''
  mensaje.value = ''

  const nuevoEstado = Number(servicio.activo) === 1 ? 0 : 1
  const accion = nuevoEstado === 1 ? 'activar' : 'inactivar'

  const confirmar = window.confirm(`¿Desea ${accion} el servicio "${servicio.nombre}"?`)
  if (!confirmar) {
    return
  }

  try {
    await cambiarEstadoServicio(servicio.id, nuevoEstado)
    mensaje.value = nuevoEstado === 1 ? 'Servicio activado correctamente' : 'Servicio inactivado correctamente'
    await cargar()
  } catch (e) {
    error.value = e.message || 'No fue posible modificar el estado del servicio'
  }
}

/*
|--------------------------------------------------------------------------
| Cargar datos al entrar a la vista
|--------------------------------------------------------------------------
*/
onMounted(cargar)
</script>

<template>
  <section class="admin-page">
    <!-- ENCABEZADO -->
    <header class="page-header">
      <div>
        <span class="page-tag">Administración</span>
        <h1>Servicios</h1>
        <p>Administra el catálogo de servicios, sus precios, duración y disponibilidad.</p>
      </div>
      <button type="button" class="btn btn-primary" @click="nuevo">+ Nuevo servicio</button>
    </header>

    <!-- MENSAJES -->
    <div v-if="mensaje" class="form-message success">
      {{ mensaje }}
    </div>
    <div v-if="error && !modal" class="form-message error">
      {{ error }}
    </div>

    <!-- CARGANDO -->
    <div v-if="loading" class="loading-box">
      Cargando servicios...
    </div>

    <!-- DATA GRID -->
    <DataGrid v-else :rows="servicios" :columns="columns" title="Reporte de Servicios" file-name="servicios">
      <!-- COLUMNA ACCIONES -->
      <template #actions="{ row }">
        <div class="row-actions">
          <button type="button" class="action-edit" title="Editar servicio" @click="editar(row)">
            Editar
          </button>
          <button type="button" class="action-status" :class="{ inactive: Number(row.activo) === 1, active: Number(row.activo) === 0 }" @click="cambiarEstado(row)">
            {{ Number(row.activo) === 1 ? 'Inactivar' : 'Activar' }}
          </button>
        </div>
      </template>
    </DataGrid>

    <!-- MODAL -->
    <div v-if="modal" class="modal-backdrop" @click.self="cerrarModal">
      <div class="modal-card">
        <!-- CABECERA MODAL -->
        <div class="modal-header">
          <div>
            <span class="page-tag modal-tag">
              {{ editando ? 'Modificar registro' : 'Nuevo registro' }}
            </span>
            <h2>
              {{ editando ? 'Editar servicio' : 'Registrar servicio' }}
            </h2>
          </div>
          <button type="button" class="modal-close" title="Cerrar" @click="cerrarModal">×</button>
        </div>

        <!-- FORMULARIO -->
        <form class="crud-form service-form" @submit.prevent="guardar">
          
          <label>
            Nombre del servicio
            <input v-model="form.nombre" type="text" maxlength="120" placeholder="Ej. Desarrollo Web" required>
          </label>

          <label>
            Precio
            <input v-model.number="form.precio" type="number" min="0" step="0.01" placeholder="0.00" required>
          </label>

          <label>
            Duración en minutos
            <input v-model.number="form.duracion_minutos" type="number" min="1" step="1" placeholder="60" required>
          </label>

          <label>
            Estado
            <select v-model.number="form.activo">
              <option :value="1">Activo</option>
              <option :value="0">Inactivo</option>
            </select>
          </label>

          <label class="full-width">
            Descripción
            <textarea v-model="form.descripcion" rows="5" placeholder="Descripción detallada del servicio..." required></textarea>
          </label>

          <div v-if="error" class="form-message error full-width">
            {{ error }}
          </div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" :disabled="saving" @click="cerrarModal">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Guardando...' : (editando ? 'Actualizar' : 'Registrar') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Estilos específicos de la vista */
.loading-box {
  padding: 2rem;
  text-align: center;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.form-message {
  padding: 0.85rem 1rem;
  border-radius: 0.65rem;
  font-weight: 600;
}

.form-message.success {
  color: #166534;
  background: #dcfce7;
  border: 1px solid #86efac;
}

.form-message.error {
  color: #991b1b;
  background: #fee2e2;
  border: 1px solid #fca5a5;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.modal-header h2 {
  margin: 0.5rem 0 0;
}

.modal-tag {
  color: #3730a3;
  background: #e0e7ff;
}

.modal-close {
  width: 40px;
  height: 40px;
  border: 0;
  border-radius: 50%;
  font-size: 1.6rem;
  line-height: 1;
  cursor: pointer;
  color: #475569;
  background: #f1f5f9;
}

.modal-close:hover {
  color: #0f172a;
  background: #e2e8f0;
}

.full-width {
  grid-column: 1 / -1;
}

.service-form textarea {
  width: 100%;
  resize: vertical;
  min-height: 120px;
}

.action-status.active {
  color: #166534;
  background: #dcfce7;
}

.action-status.inactive {
  color: #92400e;
  background: #fef3c7;
}

.btn {
  border: 0;
  padding: 0.75rem 1rem;
  border-radius: 0.6rem;
  cursor: pointer;
  font-weight: 700;
}

.btn-primary {
  color: white;
  background: #4f46e5;
}

.btn-primary:hover {
  background: #4338ca;
}

.btn-secondary {
  color: #334155;
  background: #e2e8f0;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 700px) {
  .full-width {
    grid-column: auto;
  }
}
</style>