<script setup>
import { ref } from 'vue'

defineProps({
  loading: { type: Boolean, default: false },
  error: { type: String, default: '' }
})

const emit = defineEmits(['submit'])

const usuario = ref('')
const contraseña = ref('')
const errorLocal = ref('')

function enviarFormulario() {
  errorLocal.value = ''

  if (!usuario.value.trim()) {
    errorLocal.value = 'Captura el usuario.'
    return
  }

  if (!contraseña.value) {
    errorLocal.value = 'Captura la contraseña.'
    return
  }

  emit('submit', {
    usuario: usuario.value.trim(),
    contrasena: contraseña.value
  })
}
</script>

<template>
  <form class="login-form" @submit.prevent="enviarFormulario">
    <div class="form-header">
      <span class="login-icon"></span>
      <h1>Iniciar sesión</h1>
      <p>Ingresa tus credenciales para acceder a la aplicación.</p>
    </div>

    <div class="field">
      <label for="usuario">Usuario</label>
      <input
        id="usuario"
        v-model="usuario"
        type="text"
        autocomplete="username"
        placeholder="Ej. admin"
      >
    </div>

    <div class="field">
      <label for="contrasena">Contraseña</label>
      <input
        id="contrasena"
        v-model="contraseña"
        type="password"
        autocomplete="current-password"
        placeholder="Ingresa tu contraseña"
      >
    </div>

    <p v-if="errorLocal || error" class="form-message error" role="alert">
      {{ errorLocal || error }}
    </p>

    <button class="btn btn-primary" type="submit" :disabled="loading">
      {{ loading ? 'Validando...' : 'Iniciar sesión' }}
    </button>
  </form>
</template>