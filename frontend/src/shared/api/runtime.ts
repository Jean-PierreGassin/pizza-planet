const defaultApiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api'
const defaultBackendBaseUrl = import.meta.env.VITE_BACKEND_BASE_URL ?? defaultApiBaseUrl.replace(/\/api\/?$/, '')

export const backendBaseUrl = defaultBackendBaseUrl.replace(/\/+$/, '')
