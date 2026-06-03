<?php

namespace Tests\Feature;

use App\Enums\SyncEventStatus;
use App\Enums\WebhookEventType;
use App\Jobs\SendOrderItemStatusWebhookJob;
use App\Listeners\RecordWebhookDeliveryStateListener;
use App\Models\WebhookSyncEventModel;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use RuntimeException;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;
use Tests\Support\InteractsWithWebsiteWebhookConfig;
use Tests\TestCase;

class WebhookDeliveryLedgerTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithWebsiteWebhookConfig;

    public function testWebhookJobMarksSyncEventProcessingBeforeSendAttempt(): void
    {
        Event::fake([WebhookCallSucceededEvent::class]);

        $syncEventModel = WebhookSyncEventModel::factory()->create([
            'event_type' => WebhookEventType::OrderItemStatusUpdated,
        ]);
        $client = Mockery::mock(Client::class);
        $client->expects('request')->andReturn(new Response(status: 200));
        $this->app->instance(Client::class, $client);

        $job = new SendOrderItemStatusWebhookJob();
        $job->webhookUrl = $this->websiteWebhookUrl();
        $job->httpVerb = 'post';
        $job->tries = 3;
        $job->requestTimeout = 3;
        $job->backoffStrategyClass = config('webhook-server.backoff_strategy');
        $job->headers = [];
        $job->verifySsl = true;
        $job->useTimestamp = true;
        $job->throwExceptionOnFailure = false;
        $job->payload = ['event_type' => WebhookEventType::OrderItemStatusUpdated->value];
        $job->meta = ['webhook_sync_event_id' => $syncEventModel->id];

        $job->handle();

        $syncEventModel->refresh();

        $this->assertSame(SyncEventStatus::Processing, $syncEventModel->status);
        $this->assertSame(1, $syncEventModel->attempts);
        $this->assertNotNull($syncEventModel->last_attempted_at);
    }

    public function testSuccessListenerMarksItemWebhookDelivered(): void
    {
        $syncEventModel = WebhookSyncEventModel::factory()->create([
            'event_type' => WebhookEventType::OrderItemStatusUpdated,
            'status' => SyncEventStatus::Processing,
            'last_error' => 'Previous failure',
        ]);

        app(RecordWebhookDeliveryStateListener::class)->handleWebhookCallSucceededEvent(
            event: $this->successEvent($syncEventModel),
        );

        $syncEventModel->refresh();

        $this->assertSame(SyncEventStatus::Delivered, $syncEventModel->status);
        $this->assertSame(202, $syncEventModel->response_status);
        $this->assertNull($syncEventModel->last_error);
        $this->assertNotNull($syncEventModel->delivered_at);
    }

    public function testFailureListenersRecordOrderWebhookFailureAndFinalFailure(): void
    {
        $syncEventModel = WebhookSyncEventModel::factory()->create([
            'event_type' => WebhookEventType::OrderStatusChanged,
            'status' => SyncEventStatus::Processing,
        ]);

        app(RecordWebhookDeliveryStateListener::class)->handleWebhookCallFailedEvent(
            event: $this->failedEvent($syncEventModel),
        );

        $syncEventModel->refresh();

        $this->assertSame(SyncEventStatus::Processing, $syncEventModel->status);
        $this->assertSame(500, $syncEventModel->response_status);
        $this->assertSame('Timeout waiting for website.', $syncEventModel->last_error);

        app(RecordWebhookDeliveryStateListener::class)->handleFinalWebhookCallFailedEvent(
            event: $this->finalFailedEvent($syncEventModel),
        );

        $syncEventModel->refresh();

        $this->assertSame(SyncEventStatus::Failed, $syncEventModel->status);
        $this->assertSame(500, $syncEventModel->response_status);
        $this->assertSame('Timeout waiting for website.', $syncEventModel->last_error);
    }

    private function successEvent(WebhookSyncEventModel $syncEventModel): WebhookCallSucceededEvent
    {
        return new WebhookCallSucceededEvent(
            httpVerb: 'post',
            webhookUrl: $this->websiteWebhookUrl(),
            payload: [],
            headers: [],
            meta: ['webhook_sync_event_id' => $syncEventModel->id],
            tags: [],
            attempt: 1,
            response: new Response(status: 202),
            errorType: null,
            errorMessage: null,
            uuid: 'test-uuid',
            transferStats: null,
        );
    }

    private function failedEvent(WebhookSyncEventModel $syncEventModel): WebhookCallFailedEvent
    {
        return new WebhookCallFailedEvent(
            httpVerb: 'post',
            webhookUrl: $this->websiteWebhookUrl(),
            payload: [],
            headers: [],
            meta: ['webhook_sync_event_id' => $syncEventModel->id],
            tags: [],
            attempt: 1,
            response: new Response(status: 500),
            errorType: RuntimeException::class,
            errorMessage: 'Timeout waiting for website.',
            uuid: 'test-uuid',
            transferStats: null,
        );
    }

    private function finalFailedEvent(WebhookSyncEventModel $syncEventModel): FinalWebhookCallFailedEvent
    {
        return new FinalWebhookCallFailedEvent(
            httpVerb: 'post',
            webhookUrl: $this->websiteWebhookUrl(),
            payload: [],
            headers: [],
            meta: ['webhook_sync_event_id' => $syncEventModel->id],
            tags: [],
            attempt: 3,
            response: new Response(status: 500),
            errorType: RuntimeException::class,
            errorMessage: 'Timeout waiting for website.',
            uuid: 'test-uuid',
            transferStats: null,
        );
    }
}
