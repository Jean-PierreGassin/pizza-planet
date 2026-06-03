<?php

namespace App\Services;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\DTOs\OrderItemStatusTransitionResultDTO;
use App\DTOs\UpdateOrderItemStatusDTO;
use App\Events\OrderItemStatusChangedEvent;
use App\Models\ItemStatusEvent;
use App\Models\OrderItemSyncEvent;
use App\Repositories\ItemStatusEventRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderItemSyncEventRepository;
use Illuminate\Support\Facades\DB;

class OrderItemStatusTransitionService
{
    public function __construct(
        private readonly OrderItemRepository $orderItems,
        private readonly ItemStatusEventRepository $itemStatusEvents,
        private readonly OrderItemSyncEventRepository $syncEvents,
        private readonly OrderItemStatusTransitionValidatorService $transitionValidator,
        private readonly OrderItemWebhookPayloadBuilderService $payloadBuilder,
        private readonly WebsiteWebhookConfigurationService $webhookConfiguration,
    ) {
    }

    public function transition(UpdateOrderItemStatusDTO $data): OrderItemStatusTransitionResultDTO
    {
        return DB::transaction(function () use ($data): OrderItemStatusTransitionResultDTO {
            $transition = $this->orderItems->findForStatusTransition($data);

            $this->transitionValidator->validate($transition->fromStatus, $transition->toStatus);
            $this->orderItems->updateStatus($transition->orderItem, $transition->toStatus);

            $itemStatusEvent = $this->recordStatusEvent($transition);
            $syncEvent = $this->createSyncEvent($transition, $itemStatusEvent);

            $this->dispatchAfterCommit($itemStatusEvent, $syncEvent);

            return $this->buildResult($transition, $itemStatusEvent, $syncEvent);
        });
    }

    private function recordStatusEvent(OrderItemStatusTransitionDTO $transition): ItemStatusEvent
    {
        return $this->itemStatusEvents->create($transition);
    }

    private function createSyncEvent(
        OrderItemStatusTransitionDTO $transition,
        ItemStatusEvent $itemStatusEvent,
    ): OrderItemSyncEvent {
        return $this->syncEvents->create(
            itemStatusEvent: $itemStatusEvent,
            destinationUrl: $this->webhookConfiguration->url(),
            payload: $this->payloadBuilder->build($transition, $itemStatusEvent),
        );
    }

    private function dispatchAfterCommit(
        ItemStatusEvent $itemStatusEvent,
        OrderItemSyncEvent $syncEvent,
    ): void {
        DB::afterCommit(fn () => OrderItemStatusChangedEvent::dispatch(
            itemStatusEventId: $itemStatusEvent->id,
            orderItemSyncEventId: $syncEvent->id,
        ));
    }

    private function buildResult(
        OrderItemStatusTransitionDTO $transition,
        ItemStatusEvent $itemStatusEvent,
        OrderItemSyncEvent $syncEvent,
    ): OrderItemStatusTransitionResultDTO {
        return new OrderItemStatusTransitionResultDTO(
            orderItem: $transition->orderItem->refresh(),
            status: $transition->toStatus,
            itemStatusEvent: $itemStatusEvent,
            syncEvent: $syncEvent,
        );
    }
}
