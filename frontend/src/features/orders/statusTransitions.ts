import { orderItemStatuses, type OrderItemStatus } from './types'

export function nextItemStatus (status: OrderItemStatus): OrderItemStatus | null {
  const transitions = {
    [orderItemStatuses.pending]: orderItemStatuses.preparing,
    [orderItemStatuses.preparing]: orderItemStatuses.baking,
    [orderItemStatuses.baking]: orderItemStatuses.ready,
    [orderItemStatuses.ready]: null
  } satisfies Record<OrderItemStatus, OrderItemStatus | null>

  return transitions[status]
}

export function nextItemLabel (status: OrderItemStatus): string {
  const labels = {
    [orderItemStatuses.pending]: 'Start preparing',
    [orderItemStatuses.preparing]: 'Start baking',
    [orderItemStatuses.baking]: 'Mark ready',
    [orderItemStatuses.ready]: 'Ready'
  } satisfies Record<OrderItemStatus, string>

  return labels[status]
}
