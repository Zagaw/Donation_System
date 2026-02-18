<?php

namespace App\Notifications;

use App\Models\Matches;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class MatchCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $match;
    protected $userType; // 'donor' or 'receiver'

    public function __construct(Matches $match, $userType)
    {
        $this->match = $match;
        $this->userType = $userType;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $donation = $this->match->donation;
        $request = $this->match->request;
        $interest = $this->match->interest;

        if ($this->userType === 'donor') {
            $subject = 'Your Donation Has Been Matched!';
            $greeting = 'Hello ' . ($donation?->donor?->user?->name ?? $interest?->donor?->user?->name ?? 'Donor');
            $receiverName = $request?->receiver?->user?->name ?? 'Community Member';
            $receiverPhone = $request?->receiver?->user?->phone ?? 'Not provided';
            $receiverEmail = $request?->receiver?->user?->email ?? 'Not provided';
            $receiverAddress = $request?->receiver?->user?->address ?? 'Not provided';
            
            $line1 = 'Your donation has been matched with a request. Here are the details of the receiver:';
            $actionUrl = url('/matches/' . $this->match->matchId);
        } else {
            $subject = 'Your Request Has Been Matched!';
            $greeting = 'Hello ' . ($request?->receiver?->user?->name ?? 'Receiver');
            $donorName = $donation?->donor?->user?->name ?? $interest?->donor?->user?->name ?? 'Community Member';
            $donorPhone = $donation?->donor?->user?->phone ?? $interest?->donor?->user?->phone ?? 'Not provided';
            $donorEmail = $donation?->donor?->user?->email ?? $interest?->donor?->user?->email ?? 'Not provided';
            $donorAddress = $donation?->donor?->user?->address ?? $interest?->donor?->user?->address ?? 'Not provided';
            
            $line1 = 'Your request has been matched with a donor. Here are the details of the donor:';
            $actionUrl = url('/matches/' . $this->match->matchId);
        }

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($line1)
            ->line('--- Contact Information ---')
            ->line('Name: ' . ($this->userType === 'donor' ? $receiverName : $donorName))
            ->line('Phone: ' . ($this->userType === 'donor' ? $receiverPhone : $donorPhone))
            ->line('Email: ' . ($this->userType === 'donor' ? $receiverEmail : $donorEmail))
            ->line('Address: ' . ($this->userType === 'donor' ? $receiverAddress : $donorAddress))
            ->line('--- Match Details ---')
            ->line('Item: ' . ($request?->itemName ?? 'N/A'))
            ->line('Quantity: ' . ($request?->quantity ?? 'N/A'))
            ->line('Match Type: ' . ($this->match->matchType === 'interest' ? 'Interest-Based' : 'Manual'))
            ->action('View Full Match Details', $actionUrl)
            ->line('Please contact them to arrange the donation delivery.');
    }

    public function toArray($notifiable)
    {
        $donation = $this->match->donation;
        $request = $this->match->request;
        $interest = $this->match->interest;

        if ($this->userType === 'donor') {
            return [
                'type' => 'match_created',
                'match_id' => $this->match->matchId,
                'title' => 'Your Donation Has Been Matched!',
                'message' => 'Your donation has been matched with a request. Click to view receiver details.',
                'item_name' => $donation?->itemName ?? $interest?->request?->itemName,
                'receiver' => [
                    'name' => $request?->receiver?->user?->name ?? 'Community Member',
                    'phone' => $request?->receiver?->user?->phone ?? 'Not provided',
                    'email' => $request?->receiver?->user?->email ?? 'Not provided',
                    'address' => $request?->receiver?->user?->address ?? 'Not provided',
                ],
                'match_type' => $this->match->matchType,
                'action_url' => '/matches/' . $this->match->matchId
            ];
        } else {
            return [
                'type' => 'match_created',
                'match_id' => $this->match->matchId,
                'title' => 'Your Request Has Been Matched!',
                'message' => 'Your request has been matched with a donor. Click to view donor details.',
                'item_name' => $request?->itemName,
                'donor' => [
                    'name' => $donation?->donor?->user?->name ?? $interest?->donor?->user?->name ?? 'Community Member',
                    'phone' => $donation?->donor?->user?->phone ?? $interest?->donor?->user?->phone ?? 'Not provided',
                    'email' => $donation?->donor?->user?->email ?? $interest?->donor?->user?->email ?? 'Not provided',
                    'address' => $donation?->donor?->user?->address ?? $interest?->donor?->user?->address ?? 'Not provided',
                ],
                'match_type' => $this->match->matchType,
                'action_url' => '/matches/' . $this->match->matchId
            ];
        }
    }
}