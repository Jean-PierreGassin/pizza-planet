<script setup lang="ts">
import { computed, ref } from 'vue'
import { ApiError } from '@/shared/api'
import { authApi } from '@/features/auth/api/authApi'
import type { AuthenticatedUser, LoginCredentials } from '@/features/auth/types'
import LoginPanel from '@/features/auth/components/LoginPanel.vue'
import { ordersApi } from '@/features/orders/api/ordersApi'
import type { Order, OrderItem, OrderItemStatus } from '@/features/orders/types'
import OrdersDashboard from '@/features/orders/components/OrdersDashboard.vue'

const credentials = ref<LoginCredentials>({
  email: '',
  password: ''
})

const user = ref<AuthenticatedUser | null>(null)
const orders = ref<Order[]>([])
const isLoggingIn = ref(false)
const isLoadingOrders = ref(false)
const transitioningItemId = ref<number | null>(null)
const errorMessage = ref('')

const isAuthenticated = computed(() => user.value !== null)

async function login (): Promise<void> {
  errorMessage.value = ''
  isLoggingIn.value = true

  try {
    await authApi.csrfCookie()
    user.value = (await authApi.login({
      email: credentials.value.email,
      password: credentials.value.password
    })).user
    credentials.value.password = ''
    await loadOrders()
  } catch (error) {
    errorMessage.value = isUnauthenticated(error)
      ? 'Those credentials did not open the prep station.'
      : 'Login failed. Check the API is running and try again.'
  } finally {
    isLoggingIn.value = false
  }
}

async function logout (): Promise<void> {
  await authApi.logout()
  user.value = null
  orders.value = []
  credentials.value.email = ''
  credentials.value.password = ''
}

async function loadOrders (): Promise<void> {
  isLoadingOrders.value = true

  try {
    orders.value = (await ordersApi.list()).orders
  } finally {
    isLoadingOrders.value = false
  }
}

async function moveItemForward (order: Order, item: OrderItem): Promise<void> {
  const nextStatus = nextItemStatus(item.status)

  if (nextStatus === null) {
    return
  }

  transitioningItemId.value = item.id
  errorMessage.value = ''

  try {
    const result = await ordersApi.updateItemStatus(order.id, item.id, nextStatus)
    item.status = result.status
  } catch {
    errorMessage.value = 'That item could not move to the next station yet.'
  } finally {
    transitioningItemId.value = null
  }
}

function nextItemStatus (status: OrderItemStatus): OrderItemStatus | null {
  const transitions: Record<OrderItemStatus, OrderItemStatus | null> = {
    pending: 'preparing',
    preparing: 'baking',
    baking: 'ready',
    ready: null
  }

  return transitions[status]
}

function isUnauthenticated (error: unknown): boolean {
  return error instanceof ApiError && [401, 422].includes(error.status)
}
</script>

<template>
  <main class="min-h-screen bg-[#fff8ed] text-ink">
    <section class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-6 py-6 sm:px-8">
      <header class="flex flex-wrap items-center justify-between gap-4 border-b border-ink/10 pb-5">
        <RouterLink
          to="/"
          class="text-base font-bold tracking-normal text-sauce"
        >
          Pizza Planet
        </RouterLink>

        <button
          v-if="isAuthenticated"
          type="button"
          class="rounded-md border border-ink/15 bg-white px-4 py-2 text-sm font-semibold text-ink shadow-sm transition hover:border-sauce hover:text-sauce"
          @click="logout"
        >
          Log out
        </button>
      </header>

      <LoginPanel
        v-if="!isAuthenticated"
        v-model="credentials"
        :error-message="errorMessage"
        :is-logging-in="isLoggingIn"
        @submit="login"
      />

      <OrdersDashboard
        v-else-if="user !== null"
        :error-message="errorMessage"
        :is-loading-orders="isLoadingOrders"
        :orders="orders"
        :transitioning-item-id="transitioningItemId"
        :user="user"
        @move-item-forward="moveItemForward"
        @refresh="loadOrders"
      />
    </section>
  </main>
</template>
