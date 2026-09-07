<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $mail = (new MailMessage)
            ->subject("Order {$order->number} confirmed — Pestone Technologies")
            ->greeting("Thank you, {$order->customer_name}!")
            ->line("We've received payment for order {$order->number}.");

        foreach ($order->items as $item) {
            $mail->line("• {$item->qty} × {$item->name} — KES ".number_format($item->line_total));
        }

        return $mail
            ->line('Subtotal: KES '.number_format($order->subtotal))
            ->line('VAT (16%): KES '.number_format($order->vat_total))
            ->line('Delivery: KES '.number_format($order->shipping_total))
            ->line('**Total paid: KES '.number_format($order->grand_total).'**')
            ->action('View your order', route('checkout.return', $order))
            ->line('Our team will be in touch about delivery. Questions? sales@pestone.co.ke / +254 735 120 752');
    }
}
