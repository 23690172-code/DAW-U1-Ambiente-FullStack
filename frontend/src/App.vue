<script setup>
import { ref, onMounted } from 'vue'
const estado = ref('Verificando servicios...')
const datos = ref(null)
onMounted(async () => {
try {
const respuesta = await fetch('/api/health.php')
datos.value = await respuesta.json()

estado.value = datos.value.ok
? 'Ambiente Full Stack funcionando correctamente'
: 'Se detectaron problemas'
} catch (error) {
estado.value = 'No fue posible comunicarse con el backend'
}
})
</script>
<template>
<main>
<h1>Desarrollo de Aplicaciones Web</h1>
<h2>Práctica 1 - Ambiente Full Stack</h2>
<p>{{ estado }}</p>
<div v-if="datos">
<p>Backend: {{ datos.backend }}</p>
<p>Base de datos: {{ datos.database }}</p>
</div>
</main>
</template>