<?php

namespace App\Http\Controllers;

use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Notifications\Notification;

class MyNotification extends Notification
{
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData(['key' => 'value'])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('Test Notification')
                ->setBody('This is a test notification sent from Laravel using FCM.'));
    }

}
