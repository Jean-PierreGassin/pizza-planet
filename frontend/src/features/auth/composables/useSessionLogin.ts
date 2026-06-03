import { computed, ref } from 'vue'
import { ApiError } from '@/shared/api'
import { authApi } from '../api/authApi'
import type { AuthenticatedUser, LoginCredentials } from '../types'

export function useSessionLogin () {
  const credentials = ref<LoginCredentials>({
    email: '',
    password: ''
  })

  const user = ref<AuthenticatedUser | null>(null)
  const isLoggingIn = ref(false)
  const errorMessage = ref('')

  const isAuthenticated = computed(() => user.value !== null)

  async function login (afterLogin?: () => Promise<void>): Promise<void> {
    errorMessage.value = ''
    isLoggingIn.value = true

    try {
      await authApi.csrfCookie()
      user.value = (await authApi.login({
        email: credentials.value.email,
        password: credentials.value.password
      })).user
      credentials.value.password = ''
    } catch (error) {
      errorMessage.value = isUnauthenticated(error)
        ? 'Those credentials did not open the prep station.'
        : 'Login failed. Check the API is running and try again.'
      return
    } finally {
      isLoggingIn.value = false
    }

    await afterLogin?.()
  }

  async function logout (): Promise<void> {
    try {
      await authApi.logout()
    } catch {
      // The browser should leave the authenticated UI even if the server session is already gone.
    } finally {
      user.value = null
      errorMessage.value = ''
      credentials.value.email = ''
      credentials.value.password = ''
    }
  }

  return {
    credentials,
    errorMessage,
    isAuthenticated,
    isLoggingIn,
    login,
    logout,
    user
  }
}

function isUnauthenticated (error: unknown): boolean {
  return error instanceof ApiError && [401, 422].includes(error.status)
}
