<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthorizationService
{
    protected string $url;

    public function __construct()
    {
        // URL do serviço externo de autorização
        $this->url = "https://66ad1f3cb18f3614e3b478f5.mockapi.io/v1/auth";
    }

    /**
     * Verifica se a transferência é autorizada pelo serviço externo.
     *
     * @return bool
     */
    public function check(): bool
    {
        try {
            $response = Http::withOptions([
                'verify' => storage_path('app/certs/cacert.pem'), // Caminho completo do certificado
                'timeout' => 5, // Opcional: tempo limite da requisição
            ])->get($this->url);

            if ($response->successful()) {
                $data = $response->json();

                return isset($data[0]["message"]) && $data[0]["message"] === "Autorizado";
            } else {
                Log::error("Authorization service returned an error: HTTP " . $response->status());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to connect to authorization service: " . $e->getMessage());
            return false; // Fail-safe
        }
    }
}
