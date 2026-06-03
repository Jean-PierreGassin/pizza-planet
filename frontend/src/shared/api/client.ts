import { ApiError } from './errors'
import { buildRequest, type ApiRequestOptions } from './request'
import { buildApiUrl, normalizeApiVersion, normalizeBaseUrl } from './urls'

export interface ApiClientOptions {
  baseUrl?: string
  apiVersion?: string
  credentials?: RequestCredentials
  fetcher?: typeof fetch
}

export interface ApiClient {
  get: <TResponse>(path: string, options?: ApiRequestOptions) => Promise<TResponse>
  post: <TResponse>(path: string, body: ApiRequestOptions['body'], options?: ApiRequestOptions) => Promise<TResponse>
  request: <TResponse>(path: string, options?: ApiRequestOptions) => Promise<TResponse>
}

const defaultBaseUrl = import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api'
const defaultApiVersion = import.meta.env.VITE_API_VERSION ?? 'v1'

export function createApiClient (options: ApiClientOptions = {}): ApiClient {
  const baseUrl = normalizeBaseUrl(options.baseUrl ?? defaultBaseUrl)
  const apiVersion = normalizeApiVersion(options.apiVersion ?? defaultApiVersion)
  const credentials = options.credentials ?? 'include'
  const fetcher = options.fetcher ?? fetch

  async function request<TResponse> (path: string, options: ApiRequestOptions = {}): Promise<TResponse> {
    const requestVersion = normalizeApiVersion(options.apiVersion ?? apiVersion)
    const response = await fetcher(buildApiUrl(baseUrl, path, requestVersion), buildRequest(options, credentials))

    if (!response.ok) {
      throw new ApiError(`API request failed with status ${response.status}`, response.status, response)
    }

    if (response.status === 204) {
      return undefined as TResponse
    }

    return await response.json() as TResponse
  }

  return {
    get: async <TResponse>(path: string, options: ApiRequestOptions = {}) => {
      return await request<TResponse>(path, {
        ...options,
        method: 'GET'
      })
    },
    post: async <TResponse>(path: string, body: ApiRequestOptions['body'], options: ApiRequestOptions = {}) => {
      return await request<TResponse>(path, {
        ...options,
        body,
        method: 'POST'
      })
    },
    request
  }
}

export const apiClient = createApiClient()
