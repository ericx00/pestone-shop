<?php

namespace App\Console\Commands;

use App\Services\Payments\PesapalGateway;
use Illuminate\Console\Command;

class PesapalRegisterIpn extends Command
{
    protected $signature = 'pesapal:register-ipn';

    protected $description = 'Register this site\'s IPN URL with Pesapal and cache the IPN id';

    public function handle(PesapalGateway $gateway): int
    {
        if (! $gateway->isConfigured()) {
            $this->error('Pesapal credentials are not configured (PESAPAL_CONSUMER_KEY / PESAPAL_CONSUMER_SECRET).');

            return self::FAILURE;
        }

        $id = $gateway->registerIpn();
        $this->info("Registered IPN. ipn_id = {$id}");
        $this->line('Add this to .env for cache-independent config:  PESAPAL_IPN_ID='.$id);

        return self::SUCCESS;
    }
}
