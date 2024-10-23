<?php

namespace App\Jobs;

use App\Exceptions\NotFoundException;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class UserVerificationEmail implements ShouldQueue
{
    use Queueable;

    private int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->userId = $user->id;
    }

    /**
     * Execute the job.
     * @throws NotFoundException
     */
    public function handle(): void
    {
        /** @var User $user */
        $user = User::find($this->userId);

        if (!$user) {
            throw new NotFoundException("Attempted to run UserVerificationEmail on User ID {$this->userId} however the User does not exist");
        }

        Mail::to($user->email)->send(new WelcomeEmail($user));
    }
}
