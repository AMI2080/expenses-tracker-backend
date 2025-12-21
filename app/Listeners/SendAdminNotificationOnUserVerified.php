<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewUserVerifiedNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendAdminNotificationOnUserVerified implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $verifiedUser = $event->user;

        // Ensure we have a User instance
        if (!($verifiedUser instanceof User)) {
            return;
        }

        // Get all admin users and send notifications in chunks for better performance
        User::where('is_admin', true)->chunk(100, function ($admins) use ($verifiedUser) {
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new NewUserVerifiedNotification($verifiedUser));
                } catch (\Exception $e) {
                    // Log error but continue sending to other admins
                    Log::error('Failed to send admin notification', [
                        'admin_id' => $admin->id,
                        'verified_user_id' => $verifiedUser->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}

