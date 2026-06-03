import { computed, type Ref } from 'vue'
import { orderItemStatuses, orderStatuses, type Order, type OrderItem, type OrderItemStatus, type OrderStatus } from '../types'

const itemStatusSteps = [
  orderItemStatuses.pending,
  orderItemStatuses.preparing,
  orderItemStatuses.baking,
  orderItemStatuses.ready
] satisfies OrderItemStatus[]

export function useOrderDashboard (orders: Ref<Order[]>) {
  const activeOrders = computed(() => (
    orders.value.filter((order) => !isReadyOrder(order.status))
  ))

  const readyOrders = computed(() => (
    orders.value.filter((order) => isReadyOrder(order.status))
  ))

  const orderGroups = computed(() => [
    { key: 'active', title: 'Active orders', orders: activeOrders.value },
    { key: 'ready', title: 'Ready orders', orders: readyOrders.value }
  ])

  return {
    completedItemCount,
    formatStatus,
    itemStatusSteps,
    itemStatusTone,
    itemStepState,
    orderGroups,
    orderStatusTone
  }
}

function formatStatus (status: string): string {
  return status.replaceAll('_', ' ')
}

function isReadyOrder (status: OrderStatus): boolean {
  return status === orderStatuses.readyForPickup || status === orderStatuses.readyForDelivery
}

function orderStatusTone (status: OrderStatus): string {
  const tones = {
    [orderStatuses.pending]: 'border-ink/15 bg-ink/5 text-ink/70',
    [orderStatuses.inProgress]: 'border-crust/40 bg-crust/20 text-ink',
    [orderStatuses.readyForPickup]: 'border-basil/25 bg-basil/10 text-basil',
    [orderStatuses.readyForDelivery]: 'border-basil/25 bg-basil/10 text-basil',
    [orderStatuses.completed]: 'border-ink/15 bg-white text-ink/60',
    [orderStatuses.cancelled]: 'border-sauce/25 bg-sauce/10 text-sauce'
  } satisfies Record<OrderStatus, string>

  return tones[status]
}

function itemStatusTone (status: OrderItemStatus): string {
  const tones = {
    [orderItemStatuses.pending]: 'border-ink/15 bg-white text-ink/65',
    [orderItemStatuses.preparing]: 'border-sauce/25 bg-sauce/10 text-sauce',
    [orderItemStatuses.baking]: 'border-crust/40 bg-crust/20 text-ink',
    [orderItemStatuses.ready]: 'border-basil/25 bg-basil/10 text-basil'
  } satisfies Record<OrderItemStatus, string>

  return tones[status]
}

function completedItemCount (order: Order): number {
  return order.items.filter((item) => item.status === orderItemStatuses.ready).length
}

function itemStepState (item: OrderItem, step: OrderItemStatus): string {
  const currentIndex = itemStatusSteps.indexOf(item.status)
  const stepIndex = itemStatusSteps.indexOf(step)

  if (stepIndex < currentIndex) {
    return 'bg-basil'
  }

  if (stepIndex === currentIndex) {
    return item.status === orderItemStatuses.ready ? 'bg-basil' : 'bg-sauce'
  }

  return 'bg-ink/15'
}
