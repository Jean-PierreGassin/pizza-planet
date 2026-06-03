import { applyCsrfHeader } from './csrf'

export interface ApiRequestOptions extends Omit<RequestInit, 'body'> {
  apiVersion?: string
  body?: BodyInit | Record<string, unknown>
}

export function buildRequest (options: ApiRequestOptions, credentials: RequestCredentials): RequestInit {
  const headers = new Headers(options.headers)
  applyDefaultHeaders(headers)
  applyCsrfHeader(headers, options.method)
  const body = buildBody(options.body, headers)
  const requestOptions = { ...options }
  delete requestOptions.apiVersion

  return {
    ...requestOptions,
    credentials: options.credentials ?? credentials,
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
