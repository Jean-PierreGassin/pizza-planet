<script setup lang="ts">
import type { AuthenticatedUser } from '@/features/auth/types'
import { toRef } from 'vue'
import { nextItemLabel, nextItemStatus } from '../statusTransitions'
import { useOrderDashboard } from '../composables/useOrderDashboard'
import type { Order, OrderItem } from '../types'

const props = defineProps<{
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

const {
  completedItemCount,
  formatStatus,
  itemStatusSteps,
  itemStatusTone,
  itemStepState,
  orderGroups,
  orderStatusTone
} = useOrderDashboard(toRef(props, 'orders'))
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

    <div
      v-if="orders.length === 0 && !isLoadingOrders"
      class="mt-6 rounded-md border border-ink/10 bg-white px-4 py-5 text-sm font-semibold text-ink/65 shadow-sm"
    >
      No orders are waiting right now.
    </div>

    <div
      v-else
      class="mt-6 grid gap-6"
    >
      <section
        v-for="group in orderGroups"
        v-show="group.orders.length > 0"
        :key="group.key"
      >
        <div class="mb-3 flex items-center justify-between border-b border-ink/10 pb-2">
          <h2 class="text-sm font-black uppercase tracking-normal text-ink/65">
            {{ group.title }}
          </h2>
          <p class="text-sm font-semibold text-ink/50">
            {{ group.orders.length }}
          </p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
          <article
            v-for="order in group.orders"
            :key="order.id"
            class="rounded-lg border border-ink/10 bg-white p-5 shadow-sm"
          >
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <p class="text-xs font-black uppercase tracking-normal text-ink/45">
                  Order
                </p>
                <h3 class="mt-1 text-xl font-black text-ink">
                  {{ order.reference }}
                </h3>
                <p class="mt-1 text-sm font-semibold capitalize text-ink/60">
                  {{ order.fulfillment_type }}
                </p>
              </div>

              <div class="text-right">
                <p class="text-xs font-black uppercase tracking-normal text-ink/45">
                  Order status
                </p>
                <p
                  class="mt-1 rounded-md border px-3 py-1 text-sm font-black capitalize"
                  :class="orderStatusTone(order.status)"
                >
                  {{ formatStatus(order.status) }}
                </p>
              </div>
            </div>

            <div class="mt-4 flex items-center justify-between rounded-md border border-ink/10 bg-[#fff8ed] px-3 py-2">
              <span class="text-xs font-black uppercase tracking-normal text-ink/45">Items ready</span>
              <span class="text-sm font-black text-ink">{{ completedItemCount(order) }} / {{ order.items.length }}</span>
            </div>

            <div class="mt-5">
              <div class="mb-2 flex items-center justify-between">
                <h4 class="text-xs font-black uppercase tracking-normal text-ink/45">
                  Items
                </h4>
                <p class="text-xs font-semibold text-ink/45">
                  Item status
                </p>
              </div>

              <ul class="space-y-3">
                <li
                  v-for="item in order.items"
                  :key="item.id"
                  class="grid gap-3 rounded-md border border-ink/10 p-3 sm:grid-cols-[minmax(0,1fr)_auto]"
                >
                  <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                      <p class="font-bold text-ink">
                        {{ item.name }}
                      </p>
                      <p
                        class="rounded-md border px-2 py-0.5 text-xs font-black capitalize"
                        :class="itemStatusTone(item.status)"
                      >
                        {{ formatStatus(item.status) }}
                      </p>
                    </div>

                    <ol
                      class="mt-3 grid grid-cols-4 gap-1"
                      :aria-label="`${item.name} status progress`"
                    >
                      <li
                        v-for="step in itemStatusSteps"
                        :key="step"
                        class="h-2 rounded-full"
                        :class="itemStepState(item, step)"
                      />
                    </ol>
                  </div>

                  <button
                    type="button"
                    class="h-10 min-w-36 rounded-md bg-basil px-3 text-sm font-bold text-white transition hover:bg-basil/90 disabled:cursor-not-allowed disabled:bg-ink/25"
                    :disabled="nextItemStatus(item.status) === null || transitioningItemId === item.id"
                    @click="$emit('moveItemForward', order, item)"
                  >
                    {{ transitioningItemId === item.id ? 'Moving...' : nextItemLabel(item.status) }}
                  </button>
                </li>
              </ul>
            </div>
          </article>
        </div>
      </section>
    </div>
  </section>
</template>
