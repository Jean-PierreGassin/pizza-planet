import { apiClient, type ApiClient } from '@/shared/api'
import type { OrderItemStatus, OrderItemStatusResponse, OrderResponse, OrdersResponse } from '../types'

export interface OrdersApiOptions {
  apiVersion?: string
  client?: ApiClient
}

export function createOrdersApi (options: OrdersApiOptions = {}) {
  const client = options.client ?? apiClient
  const apiVersion = options.apiVersion ?? 'v1'

  return {
    list: async (): Promise<OrdersResponse> => {
      return await client.get<OrdersResponse>('orders', { apiVersion })
    },
    show: async (orderId: number): Promise<OrderResponse> => {
      return await client.get<OrderResponse>(`orders/${orderId}`, { apiVersion })
    },
    updateItemStatus: async (
      orderId: number,
      itemId: number,
      status: OrderItemStatus
    ): Promise<OrderItemStatusResponse> => {
      return await client.request<OrderItemStatusResponse>(`orders/${orderId}/items/${itemId}`, {
        apiVersion,
        body: { status },
        method: 'PATCH'
      })
    }
  }
}

export const ordersApi = createOrdersApi()
