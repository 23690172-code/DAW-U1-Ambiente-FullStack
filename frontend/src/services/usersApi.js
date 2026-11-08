import { apiFetch } from './http'

export async function listarUsuarios() {
  const response = await apiFetch('/api/usuarios/')
  return response.data
}
export function crearUsuario(data) {
  return apiFetch('/api/usuarios/store.php', {
    method: 'POST',
    body: JSON.stringify(data)
  })
}
export function actualizarUsuario(id, data) {
  return apiFetch(`/api/usuarios/update.php?id=${id}`, {
    method: 'PUT',
    body: JSON.stringify(data)
  })
}
export function cambiarEstadoUsuario(id, activo) {
  return apiFetch(`/api/usuarios/status.php?id=${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ activo })
  })
}