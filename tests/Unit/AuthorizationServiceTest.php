<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AuthorizationService;
use Illuminate\Support\Facades\Http;

class AuthorizationServiceTest extends TestCase
{
    /** @test */
    public function it_returns_true_when_authorization_is_successful()
    {
        Http::fake([
            config("services.authorization.mock_url", "https://run.mocky.io/v3/5794d450-d2e2-4412-8131-73d0293ac1cc") => Http::response(["message" => "Autorizado"], 200)
        ]);

        $service = new AuthorizationService();
        $this->assertTrue($service->check());
    }

    /** @test */
    public function it_returns_false_when_authorization_message_is_not_autorizado()
    {
        Http::fake([
            config("services.authorization.mock_url", "https://run.mocky.io/v3/5794d450-d2e2-4412-8131-73d0293ac1cc") => Http::response(["message" => "Não Autorizado"], 200)
        ]);

        $service = new AuthorizationService();
        $this->assertFalse($service->check());
    }

    /** @test */
    public function it_returns_false_when_authorization_service_returns_an_error()
    {
        Http::fake([
            config("services.authorization.mock_url", "https://run.mocky.io/v3/5794d450-d2e2-4412-8131-73d0293ac1cc") => Http::response(null, 500)
        ]);

        $service = new AuthorizationService();
        $this->assertFalse($service->check());
    }

    /** @test */
    public function it_returns_false_when_authorization_service_connection_fails()
    {
        Http::fake([
            config("services.authorization.mock_url", "https://run.mocky.io/v3/5794d450-d2e2-4412-8131-73d0293ac1cc") => Http::response(null, -1) // Simulate connection error
        ]);

        $service = new AuthorizationService();
        $this->assertFalse($service->check());
    }
}
