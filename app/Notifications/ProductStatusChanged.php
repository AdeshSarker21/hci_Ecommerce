<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
        public string $previousStatus,
        public string $newStatus,
        public ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $productName = $this->product->name;
        $statusLabel = str_replace('_', ' ', ucfirst($this->newStatus));

        $mail = (new MailMessage)
            ->subject("Product Status Changed: {$productName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your product \"{$productName}\" has been **{$statusLabel}**.");

        if ($this->reason) {
            $mail->line("**Reason:** {$this->reason}");
        }

        if ($this->newStatus === 'published') {
            $mail->line('Your product is now live and visible to customers.');
        } elseif ($this->newStatus === 'rejected') {
            $mail->line('Please review the feedback and update your product accordingly.');
        }

        $mail->action('View Product', route('seller.products.show', $this->product))
            ->line('Thank you for using our marketplace!');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'reason' => $this->reason,
            'message' => "Product \"{$this->product->name}\" status changed to " . str_replace('_', ' ', $this->newStatus),
        ];
    }
}
