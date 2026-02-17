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
    protected $userType; // This is just a flag we pass: 'donor' or 'receiver'

    public function __construct(Matches $match, $userType)
    {
        $this->match = $match;
        $this->userType = $userType; // We set this when sending: 'donor' or 'receiver'
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $donation = $this->match->donation;
        $request = $this->match->request;
        $interest = $this->match->interest;

        // For interest-based matches, donation might be null, so we use interest data
        if ($this->userType === 'donor') {
            $subject = 'Your Donation Has Been Matched!';
            
            // Get donor name from either donation or interest
            $donorName = $donation?->donor?->user?->name ?? $interest?->donor?->user?->name ?? 'Donor';
            $greeting = 'Hello ' . $donorName;
            
            // Get item name
            $itemName = $donation?->itemName ?? $interest?->request?->itemName ?? 'your item';
            $line1 = 'Your donation "' . $itemName . '" has been matched with a request.';
            
            // Get receiver name
            $receiverName = $request?->receiver?->user?->name ?? 'a community member';
            $line2 = 'Receiver: ' . $receiverName;
            
            $line3 = 'Item: ' . ($request?->itemName ?? 'N/A');
            $line4 = 'Quantity: ' . ($request?->quantity ?? 'N/A');
            
            $actionText = 'View Match Details';
            $actionUrl = url('/donor/matches/' . $this->match->matchId);
        } else {
            $subject = 'Your Request Has Been Matched!';
            
            // Get receiver name
            $receiverName = $request?->receiver?->user?->name ?? 'Receiver';
            $greeting = 'Hello ' . $receiverName;
            
            $line1 = 'Your request for "' . $request?->itemName . '" has been matched with a donor.';
            
            // Get donor name
            $donorName = $donation?->donor?->user?->name ?? $interest?->donor?->user?->name ?? 'a community member';
            $line2 = 'Donor: ' . $donorName;
            
            $line3 = 'Item: ' . ($donation?->itemName ?? $interest?->request?->itemName ?? 'N/A');
            $line4 = 'Quantity: ' . ($donation?->quantity ?? $request?->quantity ?? 'N/A');
            
            $actionText = 'View Match Details';
            $actionUrl = url('/receiver/matches/' . $this->match->matchId);
        }

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($line1)
            ->line($line2)
            ->line($line3)
            ->line($line4)
            ->line('Match Type: ' . ($this->match->matchType === 'interest' ? 'Interest-Based' : 'Manual'))
            ->action($actionText, $actionUrl)
            ->line('Thank you for being part of our community!');
    }

    /**
     * Get the array representation of the notification for database.
     */
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
                'message' => 'Your donation has been matched with a request.',
                'item_name' => $donation?->itemName ?? $interest?->request?->itemName ?? 'Unknown item',
                'receiver_name' => $request?->receiver?->user?->name ?? 'Community Member',
                'match_type' => $this->match->matchType,
                'action_url' => '/donor/matches/' . $this->match->matchId
            ];
        } else {
            return [
                'type' => 'match_created',
                'match_id' => $this->match->matchId,
                'title' => 'Your Request Has Been Matched!',
                'message' => 'Your request has been matched with a donor.',
                'item_name' => $request?->itemName ?? 'Unknown item',
                'donor_name' => $donation?->donor?->user?->name ?? $interest?->donor?->user?->name ?? 'Community Member',
                'match_type' => $this->match->matchType,
                'action_url' => '/receiver/matches/' . $this->match->matchId
            ];
        }
    }
}