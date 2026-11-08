import { computed, readonly, ref } from 'vue'

const user = ref(null)
const initialized = ref(false)

const authenticated = computed(() => {
  return user.value !== null
})

export function useAuth() {
  async function parseJson(response) {
    const text = await response.text()
    try {
      return text ? JSON.parse(text) : {}
    } catch {
      throw new Error('Respuesta inválida del servidor')
    }
  }

  async function login(credentials) {
    const response = await fetch('/api/login.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(credentials)
    })
    const data = await parseJson(response)
    if (!response.ok) throw new Error(data.message || 'Error al iniciar sesión')
    user.value = data.user
    initialized.value = true
    return data
  }

  async function checkSession() {
    try {
      const response = await fetch('/api/session.php', { credentials: 'include' })
      const data = await parseJson(response)
      if (data.authenticated) {
        user.value = data.user
      } else {
        user.value = null
      }
    } catch {
      user.value = null
    } finally {
      initialized.value = true
    }
  }

  async function logout() {
    try {
      await fetch('/api/logout.php', {
        method: 'POST', credentials: 'include', headers: { Accept: 'application/json' }
      })
    } finally {
      user.value = null
      initialized.value = true
    }
  }

  return {
    user: readonly(user),
    initialized: readonly(initialized),
    authenticated,
    login,
    checkSession,
    logout
  }
}