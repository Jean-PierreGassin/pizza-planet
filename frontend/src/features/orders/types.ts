export const orderStatuses = {
  pending: 'pending',
  inProgress: 'in_progress',
  readyForPickup: 'ready_for_pickup',
  readyForDelivery: 'ready_for_delivery',
  completed: 'completed',
  cancelled: 'cancelled'
} as const

export type OrderStatus = typeof orderStatuses[keyof typeof orderStatuses]

export const orderFulfillmentTypes = {
  pickup: 'pickup',
  delivery: 'delivery'
} as const

export type OrderFulfillmentType = typeof orderFulfillmentTypes[keyof typeof orderFulfillmentTypes]

export const orderItemStatuses = {
  pending: 'pending',
  preparing: 'preparing',
  baking: 'baking',
  ready: 'ready'
} as const

export type OrderItemStatus = typeof orderItemStatuses[keyof typeof orderItemStatuses]

export interface Order {
  id: number
  reference: string
  fulfillment_type: OrderFulfillmentType
  status: OrderStatus
  items: OrderItem[]
}

export interface OrderItem {
  id: number
  name: string
  status: OrderItemStatus
}

export interface OrdersResponse {
  orders: Order[]
}

export interface OrderResponse {
  order: Order
}

export interface OrderItemStatusResponse {
  order_item_id: number
  status: OrderItemStatus
}
