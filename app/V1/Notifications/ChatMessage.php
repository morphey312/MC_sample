<?php

namespace App\V1\Notifications;

use Carbon\Carbon;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class ChatMessage extends Notification
{
    use Queueable;

    protected $data;
    protected $user_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'database',
            'broadcast'
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {   
        return $this->data;
    }
     /**
   * Get the broadcastable representation of the notification.
   * @param  mixed  $notifiable
   * @return BroadcastMessage
   */

    public function toBroadcast($notifiable)
    {   
        return new BroadcastMessage(array(
            'message' => $this->data,
            'type' => 'NewChatMessage'
            
        ));
    }

    public function broadcastAs()
    {
        return 'App.Notification';
    }

}
