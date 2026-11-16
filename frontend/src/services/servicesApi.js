import { apiFetch } from './http'

export async function listarServicios(activo = null) {
  let url = '/api/servicios/'
  
  if (activo !== null && activo !== '') {
    url += `?activo=${activo}`
  }
  
  const response = await apiFetch(url)
  return response.data
}

export function crearServicio(data) {
  return apiFetch('/api/servicios/store.php', {
    method: 'POST',
    body: JSON.stringify(data)
  })
}

export function actualizarServicio(id, data) {
  return apiFetch(`/api/servicios/update.php?id=${id}`, {
    method: 'PUT',
    body: JSON.stringify(data)
  })
}

export function cambiarEstadoServicio(id, activo) {
  return apiFetch(`/api/servicios/status.php?id=${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ activo })
  })
}