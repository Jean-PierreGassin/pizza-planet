<?php

namespace App\Services;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\Enums\OrderItemStatus;
use App\Events\OrderItemStatusChangedEvent;
use App\Models\OrderItemStatusEventModel;
use App\Models\WebhookSyncEventModel;
use App\Repositories\OrderItemStatusEventRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\WebhookSyncEventRepository;
use Illuminate\Support\Facades\DB;

class OrderItemStatusTransitionService
{
    public function __construct(
        private readonly OrderItemRepository $orderItems,
        private readonly OrderItemStatusEventRepository $orderItemStatusEventModels,
        private readonly WebhookSyncEventRepository $syncEventModels,
        private readonly OrderItemStatusTransitionValidatorService $transitionValidator,
        private readonly OrderStatusTransitionService $orderStatuses,
        private readonly OrderItemWebhookPayloadBuilderService $payloadBuilder,
        private readonly WebsiteWebhookConfigurationService $webhookConfiguration,
    ) {
    }

    public function transition(
        int $orderId,
        int $orderItemId,
        OrderItemStatus $status,
    ): OrderItemStatusTransitionDTO
    {
        $this->webhookConfiguration->assertConfigured();

        return DB::transaction(function () use ($orderId, $orderItemId, $status): OrderItemStatusTransitionDTO {
            $transition = $this->orderItems->findForStatusTransition(
                orderId: $orderId,
                orderItemId: $orderItemId,
                status: $status,
            );

            $this->transitionValidator->validate(
                fromStatus: $transition->fromStatus,
                toStatus: $transition->toStatus,
            );
            $transition = $this->orderItems->updateStatus(transition: $transition);

            $orderItemStatusEventModel = $this->orderItemStatusEventModels->create(transition: $transition);
            $syncEventModel = $this->createSyncEvent(
                transition: $transition,
                orderItemStatusEventModel: $orderItemStatusEventModel,
            );

            $this->orderStatuses->transitionIfReady(orderItemStatusTransition: $transition);

            $this->dispatchAfterCommit(
                orderItemStatusEventModel: $orderItemStatusEventModel,
                syncEventModel: $syncEventModel,
            );

            return $transition;
        });
    }

    private function createSyncEvent(
        OrderItemStatusTransitionDTO $transition,
        OrderItemStatusEventModel $orderItemStatusEventModel,
    ): WebhookSyncEventModel
    {
        return $this->syncEventModels->createForOrderItemStatus(
            orderItemStatusEventModel: $orderItemStatusEventModel,
            destinationUrl: $this->webhookConfiguration->url(),
            payload: $this->payloadBuilder->build(transition: $transition),
        );
    }

    private function dispatchAfterCommit(
        OrderItemStatusEventModel $orderItemStatusEventModel,
        WebhookSyncEventModel $syncEventModel,
    ): void {
        DB::afterCommit(fn (): mixed => OrderItemStatusChangedEvent::dispatch(
            orderItemStatusEventModel: $orderItemStatusEventModel,
            syncEventModel: $syncEventModel,
        ));
    }
}
