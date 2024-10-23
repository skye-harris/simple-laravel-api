<?php

namespace App\Console\Commands;

use App\Jobs\UserVerificationEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class TestVerificationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-verification-email {userId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a Verification Email for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('userId');

        /** @var User $user */
        $user = User::find($userId);
        if (!$user) {
            echo "The specified User does not exist".PHP_EOL;

            return;
        }

        // Create a v4 UUID for our validation token, we'll be storing this as a binary(16) blob for efficiency
        $uuid = Uuid::uuid4();
        $uuidBytes = $uuid->getBytes();

        $user->update(['email_verification_token' => $uuidBytes]);

        UserVerificationEmail::dispatch($user)->withoutDelay();
        echo "Dispatched a Verification email to: {$user->email}".PHP_EOL;
    }
}
