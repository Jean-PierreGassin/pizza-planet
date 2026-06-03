<?php

namespace App\Services;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\DTOs\OrderStatusTransitionDTO;
use App\Enums\OrderFulfillmentType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Events\OrderStatusChangedEvent;
use App\Models\OrderModel;
use App\Models\OrderStatusEventModel;
use App\Models\WebhookSyncEventModel;
use App\Repositories\OrderStatusEventRepository;
use App\Repositories\OrderRepository;
use App\Repositories\WebhookSyncEventRepository;
use Illuminate\Support\Facades\DB;

class OrderStatusTransitionService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderStatusEventRepository $orderStatusEvents,
        private readonly WebhookSyncEventRepository $syncEvents,
        private readonly OrderStatusWebhookPayloadBuilderService $payloadBuilder,
        private readonly WebsiteWebhookConfigurationService $webhookConfiguration,
    ) {
    }

    public function transitionIfReady(OrderItemStatusTransitionDTO $orderItemStatusTransition): void
    {
        if ($orderItemStatusTransition->toStatus !== OrderItemStatus::Ready) {
            return;
        }

        $orderStatusTransition = $this->findReadyStatusTransition(
            orderItemStatusTransition: $orderItemStatusTransition,
        );

        if ($orderStatusTransition === null) {
            return;
        }

        $orderStatusTransition = $this->orders->updateStatus(transition: $orderStatusTransition);
        $orderStatusEventModel = $this->orderStatusEvents->create(transition: $orderStatusTransition);
        $syncEventModel = $this->createSyncEvent(orderStatusEventModel: $orderStatusEventModel);

        $this->dispatchAfterCommit(
            orderStatusEventModel: $orderStatusEventModel,
            syncEventModel: $syncEventModel,
        );
    }

    private function findReadyStatusTransition(
        OrderItemStatusTransitionDTO $orderItemStatusTransition,
    ): ?OrderStatusTransitionDTO {
        $toStatus = $this->finalStatusFor(order: $orderItemStatusTransition->order);
        $orderStatusTransition = $this->orders->findForStatusTransition(
            orderId: $orderItemStatusTransition->order->id,
            toStatus: $toStatus,
        );

        if ($this->isFinalized(status: $orderStatusTransition->order->status)) {
            return null;
        }

        if ($this->orders->hasItemsNotReady(order: $orderStatusTransition->order)) {
            return null;
        }

        return $orderStatusTransition;
    }

    private function createSyncEvent(OrderStatusEventModel $orderStatusEventModel): WebhookSyncEventModel
    {
        return $this->syncEvents->createForOrderStatus(
            orderStatusEventModel: $orderStatusEventModel,
            destinationUrl: $this->webhookConfiguration->url(),
            payload: $this->payloadBuilder->build(
                orderStatusEventModel: $orderStatusEventModel,
            ),
        );
    }

    private function dispatchAfterCommit(
        OrderStatusEventModel $orderStatusEventModel,
        WebhookSyncEventModel $syncEventModel,
    ): void {
        DB::afterCommit(fn (): mixed => OrderStatusChangedEvent::dispatch(
            orderStatusEventModel: $orderStatusEventModel,
            syncEventModel: $syncEventModel,
        ));
    }

    private function isFinalized(OrderStatus $status): bool
    {
        return in_array($status, [
            OrderStatus::ReadyForPickup,
            OrderStatus::ReadyForDelivery,
        ], true);
    }

    private function finalStatusFor(OrderModel $order): OrderStatus
    {
        return match ($order->fulfillment_type) {
            OrderFulfillmentType::Pickup => OrderStatus::ReadyForPickup,
            OrderFulfillmentType::Delivery => OrderStatus::ReadyForDelivery,
        };
    }
}
