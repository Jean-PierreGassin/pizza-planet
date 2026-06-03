<script setup lang="ts">
import { computed } from 'vue'
import LoginPanel from '@/features/auth/components/LoginPanel.vue'
import { useSessionLogin } from '@/features/auth/composables/useSessionLogin'
import OrdersDashboard from '@/features/orders/components/OrdersDashboard.vue'
import { useOrders } from '@/features/orders/composables/useOrders'

const {
  credentials,
  errorMessage: sessionErrorMessage,
  isAuthenticated,
  isLoggingIn,
  login,
  logout: endSession,
  user
} = useSessionLogin()

const {
  errorMessage: orderErrorMessage,
  isLoadingOrders,
  loadOrders,
  moveItemForward,
  orders,
  resetOrders,
  transitioningItemId
} = useOrders()

const dashboardErrorMessage = computed(() => (
  sessionErrorMessage.value || orderErrorMessage.value
))

async function logout (): Promise<void> {
  await endSession()
  resetOrders()
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
        :error-message="sessionErrorMessage"
        :is-logging-in="isLoggingIn"
        @submit="login(loadOrders)"
      />

      <OrdersDashboard
        v-else-if="user !== null"
        :error-message="dashboardErrorMessage"
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
