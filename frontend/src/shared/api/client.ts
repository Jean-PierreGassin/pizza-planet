export class ApiError extends Error {
  constructor (
    message: string,
    public readonly status: number,
    public readonly response: Response
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

export interface ApiClientOptions {
  baseUrl?: string
  fetcher?: typeof fetch
}

export interface ApiRequestOptions extends Omit<RequestInit, 'body'> {
  body?: BodyInit | Record<string, unknown>
}

export interface ApiClient {
  get: <TResponse>(path: string, options?: ApiRequestOptions) => Promise<TResponse>
  post: <TResponse>(path: string, body: ApiRequestOptions['body'], options?: ApiRequestOptions) => Promise<TResponse>
  request: <TResponse>(path: string, options?: ApiRequestOptions) => Promise<TResponse>
}

const defaultBaseUrl = import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api'

export function createApiClient (options: ApiClientOptions = {}): ApiClient {
  const baseUrl = normalizeBaseUrl(options.baseUrl ?? defaultBaseUrl)
  const fetcher = options.fetcher ?? fetch

  async function request<TResponse> (path: string, options: ApiRequestOptions = {}): Promise<TResponse> {
    const response = await fetcher(buildUrl(baseUrl, path), buildRequest(options))

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

function normalizeBaseUrl (baseUrl: string): string {
  return baseUrl.replace(/\/+$/, '')
}

function buildUrl (baseUrl: string, path: string): string {
  const normalizedPath = path.replace(/^\/+/, '')

  return `${baseUrl}/${normalizedPath}`
}

function buildRequest (options: ApiRequestOptions): RequestInit {
  const headers = new Headers(options.headers)
  applyDefaultHeaders(headers)
  const body = buildBody(options.body, headers)

  return {
    ...options,
    headers,
    body
  }
}

function applyDefaultHeaders (headers: Headers): void {
  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/json')
  }
}

function buildBody (body: ApiRequestOptions['body'], headers: Headers): BodyInit | undefined {
  if (body === undefined) {
    return undefined
  }

  if (isBodyInit(body)) {
    return body
  }

  if (!headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }

  return JSON.stringify(body)
}

function isBodyInit (body: ApiRequestOptions['body']): body is BodyInit {
  return typeof body === 'string' ||
    body instanceof FormData ||
    body instanceof Blob ||
    body instanceof URLSearchParams ||
    body instanceof ArrayBuffer ||
    ArrayBuffer.isView(body) ||
    body instanceof ReadableStream
}
