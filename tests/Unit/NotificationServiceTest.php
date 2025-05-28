<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_true_when_notification_is_sent_successfully()
    {
        $user = User::factory()->create();
        $message = "Test notification";

        Http::fake([
            config("services.notification.mock_url", "https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6") => Http::response(["message" => "Success"], 200)
        ]);

        $service = new NotificationService();
        $result = $service->send($user, $message);

        $this->assertTrue($result);
        Http::assertSent(function ($request) use ($user, $message) {
            return $request->url() == config("services.notification.mock_url", "https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6") &&
                   $request["email"] == $user->email &&
                   $request["message"] == $message;
        });
    }

    /** @test */
    public function it_returns_false_when_notification_service_message_is_not_success()
    {
        $user = User::factory()->create();
        $message = "Test notification";

        Http::fake([
            config("services.notification.mock_url", "https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6") => Http::response(["message" => "Failed"], 200)
        ]);

        $service = new NotificationService();
        $this->assertFalse($service->send($user, $message));
    }

    /** @test */
    public function it_returns_false_when_notification_service_returns_an_error()
    {
        $user = User::factory()->create();
        $message = "Test notification";

        Http::fake([
            config("services.notification.mock_url", "https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6") => Http::response(null, 500)
        ]);

        $service = new NotificationService();
        $this->assertFalse($service->send($user, $message));
    }

    /** @test */
    public function it_returns_false_when_notification_service_connection_fails()
    {
        $user = User::factory()->create();
        $message = "Test notification";

        Http::fake([
            config("services.notification.mock_url", "https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6") => Http::response(null, -1) // Simulate connection error
        ]);

        $service = new NotificationService();
        $this->assertFalse($service->send($user, $message));
    }
}
