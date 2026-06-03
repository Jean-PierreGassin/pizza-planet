export const testApiBaseUrl = 'http://127.0.0.1:8000/api'

export function versionedApiUrl (path: string, apiVersion = 'v1'): string {
  return `${testApiBaseUrl}/${apiVersion}/${path.replace(/^\/+/, '')}`
}
