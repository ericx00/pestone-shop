<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * A browser-triggered stand-in for a deploy shell, for hosting with no
 * terminal/SSH/cron. Protected by DEPLOY_TOKEN in .env (a long random
 * string) — the route 404s unless it's set and matches.
 *
 * Visit:  https://pestone.co.ke/deploy/<DEPLOY_TOKEN>
 * Options (query string): ?reimport=1 to re-run the catalogue import even
 * if products already exist; ?seed=0 to skip seeding.
 */
class DeployController extends Controller
{
    public function run(Request $request, string $token): Response
    {
        $expected = (string) config('app.deploy_token');
        abort_unless($expected !== '' && hash_equals($expected, $token), 404);

        $lines = [];
        $log = function (string $line) use (&$lines) {
            $lines[] = $line;
            Log::info('[deploy] '.$line);
        };
        $step = function (string $label, \Closure $fn) use (&$lines, $log) {
            $log("→ {$label}");
            try {
                $fn();
                if ($out = trim(Artisan::output())) {
                    foreach (explode("\n", $out) as $outLine) {
                        $log('    '.$outLine);
                    }
                }
                $log('  ok');
            } catch (\Throwable $e) {
                $log('  FAILED: '.$e->getMessage());
            }
        };

        $step('key:generate (only if missing)', function () {
            if (! config('app.key')) {
                Artisan::call('key:generate', ['--force' => true]);
            }
        });

        $step('migrate --force', fn () => Artisan::call('migrate', ['--force' => true]));

        if ($request->boolean('seed', true)) {
            $step('db:seed --force', fn () => Artisan::call('db:seed', ['--force' => true]));
        }

        $step('catalog:import', function () use ($request) {
            $opts = [];
            if ($request->boolean('reimport')) {
                $opts['--fresh'] = true;
            } else {
                $opts['--if-empty'] = true;
            }
            Artisan::call('catalog:import', $opts);
        });

        $step('publish storage symlink into the web root', function () {
            $target = storage_path('app/public');
            $webRoot = rtrim((string) config('app.web_root'), '/');
            if (! $webRoot) {
                throw new \RuntimeException('WEB_ROOT is not set in .env');
            }
            $link = $webRoot.'/storage';
            if (is_link($link) || file_exists($link)) {
                @unlink($link);
            }
            if (! @symlink($target, $link)) {
                throw new \RuntimeException("symlink() failed — create it manually via File Manager if your host disables it.");
            }
        });

        if ($request->boolean('pesapal_ipn')) {
            $step('pesapal:register-ipn', fn () => Artisan::call('pesapal:register-ipn'));
        }

        $step('optimize:clear', fn () => Artisan::call('optimize:clear'));
        $step('config:cache', fn () => Artisan::call('config:cache'));
        $step('route:cache', fn () => Artisan::call('route:cache'));
        $step('view:cache', fn () => Artisan::call('view:cache'));
        $step('filament:optimize', fn () => Artisan::call('filament:optimize'));

        $lines[] = '';
        $lines[] = 'Done. Reload pestone.co.ke to check the site.';

        return response(implode("\n", $lines))->header('Content-Type', 'text/plain');
    }
}
