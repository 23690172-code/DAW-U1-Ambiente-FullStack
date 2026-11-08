export async function apiFetch(url, options = {}) {
  const response = await fetch(url, {
    credentials: 'include',
    ...options,
    headers: {
      Accept: 'application/json',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
      ...options.headers
    }
  });
  const text = await response.text();
  let data = {};
  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    throw new Error('El servidor no devolvió JSON válido');
  }
  if (!response.ok) {
    const error = new Error(data.message || `Error HTTP ${response.status}`);
    error.status = response.status;
    error.errors = data.errors || {};
    throw error;
  }
  return data;
}