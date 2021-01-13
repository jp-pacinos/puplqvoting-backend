<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VoteEmailVerification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $voteHistoryId;

    /**
     * Create a new notification instance.
     * @param int $sessionId
     *
     * @return void
     */
    public function __construct($voteHistoryId)
    {
        $this->voteHistoryId = $voteHistoryId;
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
     * @param  \App\Models\UserStudent|mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = URL::temporarySignedRoute(
            'vote.final.verified.email',
            now()->addDay(),
            ['history' => $this->voteHistoryId]
        );

        return (new MailMessage)
            ->greeting('Hello '.$notifiable->firstname.'!')
            ->line('You have received this message to verify your vote.')
            ->line('If you are unaware that you made this, please simply ingnore this message.')
            ->action('Verify my vote', $url)
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
