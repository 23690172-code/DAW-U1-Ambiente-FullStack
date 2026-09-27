const API_URL = 'http://localhost:8080/api'

export async function obtenerServicios() {
  const respuesta = await fetch(`${API_URL}/servicios.php`)

  if (!respuesta.ok) {
    throw new Error(`Error al consultar servicios: ${respuesta.status}`)
  }

  return await respuesta.json()
}