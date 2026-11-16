import { apiFetch } from './http'

export async function listarMisSolicitudes() {
  const r = await apiFetch('/api/solicitudes/mine.php')
  return r.data
}

export function crearSolicitud(data) {
  return apiFetch('/api/solicitudes/store.php', {
    method: 'POST',
    body: JSON.stringify(data)
  })
}

export async function listarSolicitudes() {
  const r = await apiFetch('/api/solicitudes/index.php') 
  return r.data
}

export const autorizarSolicitud = (id) =>
  apiFetch(`/api/solicitudes/authorize.php?id=${id}`, { method: 'PATCH' })

export const rechazarSolicitud = (id, observaciones) =>
  apiFetch(`/api/solicitudes/reject.php?id=${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ observaciones })
  })

export const programarSolicitud = (id, data) =>
  apiFetch(`/api/solicitudes/schedule.php?id=${id}`, {
    method: 'PATCH',
    body: JSON.stringify(data)
  })

export const iniciarSolicitud = (id) =>
  apiFetch(`/api/solicitudes/start.php?id=${id}`, { method: 'PATCH' })

export const completarSolicitud = (id, observaciones = '') =>
  apiFetch(`/api/solicitudes/complete.php?id=${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ observaciones })
  })

export const cancelarSolicitud = (id, observaciones = '') =>
  apiFetch(`/api/solicitudes/cancel.php?id=${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ observaciones })
  })

export async function obtenerHistorial(id) {
  const r = await apiFetch(`/api/solicitudes/history.php?id=${id}`)
  return r.data
}
