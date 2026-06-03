<script setup lang="ts">
import type { AuthenticatedUser } from '@/features/auth/types'
import { nextItemLabel, nextItemStatus } from '../statusTransitions'
import type { Order, OrderItem } from '../types'

defineProps<{
  errorMessage: string
  isLoadingOrders: boolean
  orders: Order[]
  transitioningItemId: number | null
  user: AuthenticatedUser
}>()

defineEmits<{
  refresh: []
  moveItemForward: [order: Order, item: OrderItem]
}>()
</script>

<template>
  <section class="py-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-sm font-semibold uppercase tracking-normal text-basil">
          Orders
        </p>
        <h1 class="mt-2 text-3xl font-black leading-tight tracking-normal text-ink sm:text-4xl">
          Welcome back, {{ user.name }}.
        </h1>
      </div>

      <button
        type="button"
        class="rounded-md border border-ink/15 bg-white px-4 py-2 text-sm font-semibold text-ink shadow-sm transition hover:border-basil hover:text-basil"
        :disabled="isLoadingOrders"
        @click="$emit('refresh')"
      >
        {{ isLoadingOrders ? 'Refreshing...' : 'Refresh' }}
      </button>
    </div>

    <p
      v-if="errorMessage"
      class="mt-5 rounded-md border border-sauce/25 bg-sauce/5 px-3 py-2 text-sm font-semibold text-sauce"
      role="alert"
    >
      {{ errorMessage }}
    </p>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
      <article
        v-for="order in orders"
        :key="order.id"
        class="rounded-lg border border-ink/10 bg-white p-5 shadow-sm"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h2 class="text-xl font-black text-ink">
              {{ order.reference }}
            </h2>
            <p class="mt-1 text-sm font-semibold capitalize text-ink/60">
              {{ order.fulfillment_type }} · {{ order.status.replaceAll('_', ' ') }}
            </p>
          </div>
        </div>

        <ul class="mt-5 space-y-3">
          <li
            v-for="item in order.items"
            :key="item.id"
            class="grid gap-3 rounded-md border border-ink/10 p-3 sm:grid-cols-[1fr_auto]"
          >
            <div>
              <p class="font-bold text-ink">
                {{ item.name }}
              </p>
              <p class="mt-1 text-sm font-semibold capitalize text-ink/55">
                {{ item.status.replaceAll('_', ' ') }}
              </p>
            </div>

            <button
              type="button"
              class="min-w-36 rounded-md bg-basil px-3 py-2 text-sm font-bold text-white transition hover:bg-basil/90 disabled:cursor-not-allowed disabled:bg-ink/25"
              :disabled="nextItemStatus(item.status) === null || transitioningItemId === item.id"
              @click="$emit('moveItemForward', order, item)"
            >
              {{ transitioningItemId === item.id ? 'Moving...' : nextItemLabel(item.status) }}
            </button>
          </li>
        </ul>
      </article>
    </div>
  </section>
</template>
