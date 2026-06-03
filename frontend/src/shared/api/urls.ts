export function normalizeBaseUrl (baseUrl: string): string {
  return baseUrl.replace(/\/+$/, '')
}

export function normalizeApiVersion (apiVersion: string): string {
  return apiVersion.replace(/^\/+|\/+$/g, '')
}

export function buildApiUrl (baseUrl: string, path: string, apiVersion: string): string {
  const normalizedPath = path.replace(/^\/+/, '')

  return `${baseUrl}/${apiVersion}/${normalizedPath}`
}
