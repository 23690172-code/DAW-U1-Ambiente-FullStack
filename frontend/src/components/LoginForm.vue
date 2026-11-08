<script setup>
import { ref } from 'vue'

defineProps({
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['submit'])

const usuario = ref('')
const contrasena = ref('')
const errorLocal = ref('')

function enviarFormulario() {
  errorLocal.value = ''

  if (!usuario.value.trim()) {
    errorLocal.value = 'Captura el usuario.'
    return
  }

  if (!contrasena.value) {
    errorLocal.value = 'Captura la contraseña.'
    return
  }

  emit('submit', {
    usuario: usuario.value.trim(),
    contrasena: contrasena.value
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
        v-model="contrasena"
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

<style scoped>
.login-form {
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
}

.form-header {
  text-align: center;
  margin-bottom: 2rem;
}

.field {
  margin-bottom: 1rem;
}

.field label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.field input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 0.5rem;
}

.btn-primary {
  width: 100%;
  padding: 0.75rem;
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: bold;
}

.form-message.error {
  color: red;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}
</style>