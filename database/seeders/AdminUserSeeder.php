<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the admin account once. Deliberately does NOT touch the
     * password (or overwrite an existing user) on later runs — this seeder
     * re-runs on every deploy, and a re-seed silently reverting a password
     * the admin changed since would be a nasty surprise.
     */
    public function run(): void
    {
        $email = config('app.admin.email');

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->forceFill(['is_admin' => true])->save();

            return;
        }

        User::create([
            'name' => config('app.admin.name'),
            'email' => $email,
            // No ADMIN_PASSWORD configured? Generate a random one rather than
            // a guessable literal default — check storage/logs if you need it.
            'password' => Hash::make(config('app.admin.password') ?: tap(Str::random(20), function ($p) {
                logger()->warning("AdminUserSeeder: ADMIN_PASSWORD not set, generated random password: {$p}");
            })),
            'is_admin' => true,
            'type' => 'b2c',
            'email_verified_at' => now(),
        ]);
    }
}
