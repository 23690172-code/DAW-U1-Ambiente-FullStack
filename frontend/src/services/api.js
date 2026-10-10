const API_URL = '/api'

export async function obtenerServicios() {
  const response = await fetch(`${API_URL}/servicios.php`, {
    credentials: 'include',
    headers: {
      'Accept': 'application/json'
    }
  })
  const contentType = response.headers.get('content-type') || ''
  if (!contentType.includes('application/json')) {
    throw new Error('El servidor no devolvió JSON.')
  }
  const data = await response.json()
  if (response.status === 401) {
    throw new Error('La sesión no es válida.')
  }
  if (!response.ok) {
    throw new Error(data.message || 'Error al consultar servicios.')
  }
  return data
}