import { ref } from 'vue'
import { ordersApi } from '../api/ordersApi'
import { nextItemStatus } from '../statusTransitions'
import type { Order, OrderItem } from '../types'

export function useOrders () {
  const orders = ref<Order[]>([])
  const isLoadingOrders = ref(false)
  const transitioningItemId = ref<number | null>(null)
  const errorMessage = ref('')

  async function loadOrders (): Promise<void> {
    isLoadingOrders.value = true
    errorMessage.value = ''

    try {
      orders.value = (await ordersApi.list()).orders
    } catch {
      orders.value = []
      errorMessage.value = 'Orders could not load. Check the API is running and refresh.'
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
      return
    } finally {
      transitioningItemId.value = null
    }

    await loadOrders()
  }

  function resetOrders (): void {
    orders.value = []
    errorMessage.value = ''
    transitioningItemId.value = null
  }

  return {
    errorMessage,
    isLoadingOrders,
    loadOrders,
    moveItemForward,
    orders,
    resetOrders,
    transitioningItemId
  }
}
