import { computed, readonly, ref } from 'vue'

const user = ref(null)
const initialized = ref(false)
const authenticated = computed(() => {
  return user.value !== null
})

async function parseJson(response) {
  const contentType = response.headers.get('content-type') || ''
  if (!contentType.includes('application/json')) {
    throw new Error('El servidor no devolvió JSON.')
  }
  return await response.json()
}

async function login(credentials) {
  const response = await fetch('/api/login.php', {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(credentials)
  })

  const data = await parseJson(response)

  if (!response.ok || !data.ok) {
    throw new Error(data.message || 'No fue posible iniciar sesión.')
  }

  user.value = data.user
  initialized.value = true
  return data.user
}

async function checkSession() {
  try {
    const response = await fetch('/api/session.php', {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    })
    const data = await parseJson(response)
    user.value = data.authenticated ? data.user : null
  } catch (e) {
    user.value = null
  } finally {
    initialized.value = true
  }
}

async function logout() {
  try {
    await fetch('/api/logout.php', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Accept': 'application/json'
      }
    })
  } finally {
    user.value = null
    initialized.value = true
  }
}

export function useAuth() {
  return {
    user: readonly(user),
    initialized: readonly(initialized),
    authenticated,
    login,
    checkSession,
    logout
  }
}