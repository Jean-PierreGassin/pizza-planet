import { apiClient, backendBaseUrl, type ApiClient } from '@/shared/api'
import type { AuthResponse, LoginCredentials } from '../types'

export interface AuthApiOptions {
  client?: ApiClient
  fetcher?: typeof fetch
}

export function createAuthApi (options: AuthApiOptions = {}) {
  const client = options.client ?? apiClient
  const fetcher = options.fetcher ?? fetch

  return {
    csrfCookie: async (): Promise<void> => {
      const response = await fetcher(`${backendBaseUrl}/sanctum/csrf-cookie`, {
        credentials: 'include',
        headers: {
          Accept: 'application/json'
        }
      })

      if (!response.ok) {
        throw new Error(`CSRF cookie request failed with status ${response.status}`)
      }
    },
    login: async (credentials: LoginCredentials): Promise<AuthResponse> => {
      return await client.post<AuthResponse>('sessions', { ...credentials })
    },
    logout: async (): Promise<void> => {
      await client.request<void>('session', { method: 'DELETE' })
    },
    currentSession: async (): Promise<AuthResponse> => {
      return await client.get<AuthResponse>('session')
    }
  }
}

export const authApi = createAuthApi()
