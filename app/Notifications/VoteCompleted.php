<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VoteCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public $resultUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($resultUrl)
    {
        $this->resultUrl = $resultUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting('Hello again ' . $notifiable->firstname . '!')
            ->line('First of all thank you for voting, in case you missed to download your voting results you can download it here.')
            ->line('Please be aware that the link will expire within this day.')
            ->action('Download Vote Result', $this->resultUrl)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
