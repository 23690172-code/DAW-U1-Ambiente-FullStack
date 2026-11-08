<script setup>
import {computed, onMounted, ref} from 'vue'
import {listarServicios} from '../services/servicesApi'
const servicios = ref([])
const loading = ref(false)
const error = ref('')
const busqueda = ref('')
async function cargarServicios() {
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
const serviciosFiltrados =
computed(() => {
const texto = busqueda.value
.trim()
.toLowerCase()
if (!texto) {
return servicios.value
}
return servicios.value.filter(
servicio => {
const nombre =

servicio.nombre
?.toLowerCase()
?? ''
const descripcion =
servicio.descripcion
?.toLowerCase()
?? ''
return (nombre.includes(texto) || descripcion.includes(texto))
}
)
})
function formatearPrecio(precio) {
const numero = Number(precio)
if (Number.isNaN(numero)) {
return '$0.00'
}
return numero.toLocaleString('es-MX',
{
style: 'currency',
currency: 'MXN'
}
)
}

function formatearDuracion(minutos) {
const total = Number(minutos)
if (Number.isNaN(total)) {
return ''
}
if (total < 60) {
return `${total} min`
}
const horas = Math.floor(total / 60)
const restantes = total % 60
if (restantes === 0) {
return horas === 1
? '1 hora'
: `${horas} horas`
}
return (`${horas} h ${restantes} min`)
}
onMounted(cargarServicios)
</script>
<template>
<section class="services-page">
<header class="services-hero">
<div class="hero-content">
<span class="hero-tag">
Catálogo
</span>
<h1>
Nuestros servicios

</h1>
<p>
Consulta los servicios disponibles
y encuentra la opción que mejor se
adapte a tus necesidades.
</p>
</div>
</header>
<section class="services-toolbar">
<label
class="search-box"
>
<span>
Buscar servicio
</span>
<input
v-model="busqueda"
type="search"
placeholder="Buscar por nombre o descripción..."
>
</label>
<div
class="services-counter"
>
Servicios disponibles:
<strong>
{{ serviciosFiltrados.length }}
</strong>
</div>
</section>
<div
v-if="loading"
class="state-box"
>
<div
class="loader"
></div>
<p>
Consultando servicios...
</p>
</div>
<div
v-else-if="error"
class="state-box error-box"
role="alert"
>
<h2>
No fue posible cargar los servicios
</h2>
<p>
{{ error }}
</p>
<button
type="button"

class="btn-retry"
@click="cargarServicios"
>
Intentar nuevamente
</button>
</div>
<div
v-else-if="
serviciosFiltrados.length === 0
"
class="state-box"
>
<h2>
No existen servicios disponibles
</h2>
<p
v-if="busqueda"
>
No se encontraron resultados para
"{{ busqueda }}".
</p>
<p
v-else
>
Actualmente no hay servicios activos.
</p>
</div>
<div
v-else
class="services-grid"
>
<article
v-for="
servicio
in serviciosFiltrados
"
:key="servicio.id"
class="service-card"
>
<div
class="service-card-header"
>
<div
class="service-icon"
>
⚙
</div>
<span
class="status-badge"
>
Disponible
</span>
</div>
<div

class="service-card-body"
>
<h2>
{{ servicio.nombre }}
</h2>
<p
class="service-description"
>
{{ servicio.descripcion }}
</p>
<div
class="service-details"
>
<div>
<span
class="detail-label"
>
Precio
</span>
<strong
class="service-price"
>
{{
formatearPrecio(
servicio.precio
)
}}
</strong>
</div>
<div>
<span
class="detail-label"
>
Duración
</span>
<strong>
{{
formatearDuracion(
servicio.duracion_minutos
)
}}
</strong>
</div>
</div>
</div>
<footer
class="service-card-footer"
>
<button
type="button"
class="btn-service"
disabled
>
Contratación en siguiente etapa

</button>
</footer>
</article>
</div>
</section>
</template>
<style scoped>
.services-page {
display: grid;
gap: 1.75rem;
}
.services-hero {
position: relative;
overflow: hidden;
padding:
3rem 2.5rem;
border-radius:
1.5rem;
color: white;
background:
linear-gradient(
135deg,
#312e81,
#4f46e5 50%,
#0891b2
);
}
.services-hero::after {
content: '';
position: absolute;
width: 280px;
height: 280px;
right: -70px;
top: -100px;
border-radius: 50%;
background:
rgba(
255,
255,
255,
0.08
);
}
.hero-content {
position: relative;
z-index: 1;
max-width:
650px;
}
.hero-tag {
display:
inline-block;
padding:

0.4rem 0.85rem;
border-radius:
999px;
font-size:
0.85rem;
font-weight:
700;
background:
rgba(
255,
255,
255,
0.16
);
}
.services-hero h1 {
margin:
0.7rem 0 0.5rem;
font-size:
clamp(
2rem,
5vw,
3.2rem
);
}
.services-hero p {
margin: 0;
max-width:
580px;
line-height:
1.6;
opacity:
0.9;
}
.services-toolbar {
display: flex;
align-items:
flex-end;
justify-content:
space-between;
gap: 1rem;
padding:
1.25rem;
border-radius:
1rem;
background:
white;
box-shadow:
0 10px 30px
rgba(
15,
23,
42,
0.07

);
}
.search-box {
display: grid;
gap: 0.4rem;
width:
min(
480px,
100%
);
color:
#334155;
font-weight:
600;
}
.search-box input {
width: 100%;
box-sizing:
border-box;
padding:
0.8rem 1rem;
border:
1px solid #cbd5e1;
border-radius:
0.65rem;
font: inherit;
}
.search-box input:focus {
outline:
3px solid
rgba(
79,
70,
229,
0.15
);
border-color:
#6366f1;
}
.services-counter {
color:
#64748b;
}
.services-grid {
display: grid;
grid-template-columns:
repeat(
auto-fit,
minmax(
270px,
1fr
)
);
gap:

1.3rem;
}
.service-card {
display: flex;
flex-direction:
column;
overflow: hidden;
min-height:
330px;
border:
1px solid #e2e8f0;
border-radius:
1.2rem;
background:
white;
box-shadow:
0 8px 25px
rgba(
15,
23,
42,
0.06
);
transition:
transform 0.2s ease,
box-shadow 0.2s ease;
}
.service-card:hover {
transform:
translateY(-4px);
box-shadow:
0 16px 35px
rgba(
15,
23,
42,
0.12
);
}
.service-card-header {
display: flex;
align-items: center;
justify-content:
space-between;
padding:
1.2rem 1.2rem 0;
}
.service-icon {
display: grid;
place-items: center;
width: 46px;
height: 46px;
border-radius:
0.8rem;

color: white;
font-size:
1.3rem;
background:
linear-gradient(
135deg,
#4f46e5,
#0891b2
);
}
.status-badge {
padding:
0.35rem 0.7rem;
border-radius:
999px;
color:
#166534;
background:
#dcfce7;
font-size:
0.78rem;
font-weight:
700;
}
.service-card-body {
flex: 1;
padding:
1.25rem;
}
.service-card-body h2 {
margin:
0 0 0.7rem;
color:
#0f172a;
font-size:
1.3rem;
}
.service-description {
min-height:
70px;
margin:
0;
color:
#64748b;
line-height:
1.55;
}
.service-details {
display: grid;
grid-template-columns:
1fr 1fr;
gap: 1rem;
margin-top:
1.4rem;

padding-top:
1.1rem;
border-top:
1px solid #e2e8f0;
}
.service-details > div {
display: grid;
gap: 0.25rem;
}
.detail-label {
color:
#94a3b8;
font-size:
0.75rem;
text-transform:
uppercase;
letter-spacing:
0.05em;
}
.service-price {
color:
#4f46e5;
font-size:
1.1rem;
}
.service-card-footer {
padding:
0 1.25rem 1.25rem;
}
.btn-service {
width: 100%;
padding:
0.75rem;
border: 0;
border-radius:
0.65rem;
font-weight:
700;
color:
#64748b;
background:
#f1f5f9;
cursor:
not-allowed;
}
.state-box {
display: grid;
place-items: center;
gap:
0.75rem;
min-height:
220px;
padding:
2rem;

text-align:
center;
border-radius:
1rem;
background:
white;
box-shadow:
0 10px 30px
rgba(
15,
23,
42,
0.07
);
}
.state-box h2,
.state-box p {
margin: 0;
}
.error-box {
color:
#991b1b;
}
.btn-retry {
padding:
0.65rem 1rem;
border: 0;
border-radius:
0.6rem;
color: white;
background:
#4f46e5;
cursor:
pointer;
}
.loader {
width: 38px;
height: 38px;
border:
4px solid #e2e8f0;
border-top-color:
#4f46e5;
border-radius:
50%;
animation:
spin 0.8s linear infinite;
}
@keyframes spin {
to {
transform:
rotate(360deg);
}
}
@media (

max-width: 700px
) {
.services-hero {
padding:
2rem 1.5rem;
}
.services-toolbar {
align-items:
stretch;
flex-direction:
column;
}
.services-grid {
grid-template-columns:
1fr;
}
}
</style>