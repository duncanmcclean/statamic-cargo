<?php

namespace DuncanMcClean\Cargo\Notifications;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerOrderShipped extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(protected Order $order) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail',
        ];
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
            ->subject(__(':siteName: Order Confirmation', ['siteName' => config('app.name')]))
            ->markdown('cargo::emails.customer_order_shipped', [
                'order' => $this->order,
                'site' => $this->order->site(),
            ]);
    }
}
