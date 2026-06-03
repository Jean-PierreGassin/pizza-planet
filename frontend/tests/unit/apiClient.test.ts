import { createApiClient } from '@/shared/api'
import { testApiBaseUrl, versionedApiUrl } from '../support/apiUrls'

describe('createApiClient', () => {
  it('adds the default API version and credentials to requests', async () => {
    const fetcher = vi.fn().mockResolvedValue(new Response(JSON.stringify({ orders: [] })))
    const client = createApiClient({
      baseUrl: testApiBaseUrl,
      apiVersion: 'v1',
      fetcher
    })

    await client.get('orders')

    expect(fetcher).toHaveBeenCalledWith(versionedApiUrl('orders'), expect.objectContaining({
      credentials: 'include',
      method: 'GET'
    }))
  })

  it('can swap API versions per request', async () => {
    const fetcher = vi.fn().mockResolvedValue(new Response(JSON.stringify({ orders: [] })))
    const client = createApiClient({
      baseUrl: testApiBaseUrl,
      apiVersion: 'v1',
      fetcher
    })

    await client.get('orders', { apiVersion: 'v2' })

    expect(fetcher).toHaveBeenCalledWith(versionedApiUrl('orders', 'v2'), expect.any(Object))
  })

  it('adds the XSRF cookie value to state-changing requests', async () => {
    document.cookie = 'XSRF-TOKEN=csrf-token-value'
    const fetcher = vi.fn().mockResolvedValue(new Response(null, { status: 204 }))
    const client = createApiClient({
      baseUrl: testApiBaseUrl,
      fetcher
    })

    await client.request('session', { method: 'DELETE' })

    const request = fetcher.mock.calls[0][1] as RequestInit
    const headers = request.headers as Headers

    expect(headers.get('X-XSRF-TOKEN')).toBe('csrf-token-value')
  })
})
