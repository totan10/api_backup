<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class GeneralNotification extends Notification
{
    public function via($notifiable)
    {
        return ['firebase'];
    }

    public function toFirebase($notifiable)
    {
        return CloudMessage::withTarget('token', $notifiable->device_token)
            ->withNotification(FcmNotification::create('Title', 'Body'));
    }
}
