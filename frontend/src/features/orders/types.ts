export type OrderStatus =
  | 'pending'
  | 'in_progress'
  | 'ready_for_pickup'
  | 'ready_for_delivery'
  | 'completed'
  | 'cancelled'

export type OrderFulfillmentType =
  | 'pickup'
  | 'delivery'

export type OrderItemStatus =
  | 'pending'
  | 'preparing'
  | 'baking'
  | 'ready'

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
