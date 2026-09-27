<script setup>
import { computed, onMounted, ref } from 'vue'
import ServiceCard from '../components/ServiceCard.vue'
import { obtenerServicios } from '../services/api'

const servicios = ref([])
const busqueda = ref('')
const cargando = ref(true)
const error = ref('')
const servicioSeleccionado = ref(null)

const serviciosFiltrados = computed(() => {
  const texto = busqueda.value.toLowerCase()
  return servicios.value.filter(servicio =>
    servicio.nombre.toLowerCase().includes(texto)
  )
})

function seleccionarServicio(servicio) {
  servicioSeleccionado.value = servicio
  alert(`Seleccionaste: ${servicio.nombre} - $${servicio.precio}`)
}

onMounted(async () => {
  try {
    servicios.value = await obtenerServicios()
  } catch (e) {
    error.value = e.message
  } finally {
    cargando.value = false
  }
})
</script>

<template>
  <section>
    <header class="section-header">
      <div>
        <h2>Servicios disponibles</h2>
        <p>Consulta los servicios disponibles.</p>
      </div>
      <label>
        Buscar servicio
        <input
          v-model="busqueda"
          type="search"
          placeholder="Ej. desarrollo web"
          aria-label="Buscar servicio por nombre"
        />
      </label>
    </header>

    <p v-if="cargando" aria-live="polite">Cargando información...</p>

    <p v-else-if="error" role="alert">{{ error }}</p>

    <p v-else-if="serviciosFiltrados.length === 0">
      No existen servicios que coincidan con la búsqueda.
    </p>

    <div v-else class="services-grid">
      <ServiceCard
        v-for="servicio in serviciosFiltrados"
        :key="servicio.id"
        :servicio="servicio"
        @seleccionar="seleccionarServicio"
      />
    </div>
  </section>
</template>