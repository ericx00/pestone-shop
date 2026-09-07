<?php

namespace App\Services\Payments;

use App\Models\Setting;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PaymentManager
{
    /** @var array<string, class-string<PaymentGateway>> */
    protected array $map = [
        'mpesa' => MpesaGateway::class,
        'pesapal' => PesapalGateway::class,
    ];

    public function gateway(string $key): PaymentGateway
    {
        if (! isset($this->map[$key])) {
            throw new InvalidArgumentException("Unknown payment gateway [{$key}].");
        }

        return app($this->map[$key]);
    }

    /** Gateways enabled in settings AND configured with credentials. */
    public function available(): Collection
    {
        $enabled = (array) Setting::value('payment_methods', ['mpesa', 'pesapal']);

        return collect($this->map)
            ->keys()
            ->filter(fn (string $key) => in_array($key, $enabled, true))
            ->map(fn (string $key) => $this->gateway($key))
            ->filter(fn (PaymentGateway $g) => $g->isConfigured())
            ->values();
    }

    public function all(): Collection
    {
        return collect($this->map)->keys()->map(fn ($k) => $this->gateway($k));
    }
}
