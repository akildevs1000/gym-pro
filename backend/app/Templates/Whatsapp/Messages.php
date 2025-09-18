<?php
namespace App\Templates\Whatsapp;

class Messages
{
    // Keys as constants
    public const ACCOUNT_CREATED      = 'account_created';
    public const ACCOUNT_UPDATED      = 'account_updated';
    public const PAYMENT_RECEIVED     = 'payment_received';
    public const SUBSCRIPTION_EXPIRED = 'subscription_expired';
    public const MEMBERSHIP_UPDATED   = 'membership_updated';
    public const MEMBERSHIP_RENEWED   = 'membership_renewed';

    // Message templates
    public const TEMPLATES = [
        self::ACCOUNT_CREATED      => "Welcome to Gym Pro! Your account has been successfully created.\n\nRegards,\nGym Pro",
        self::ACCOUNT_UPDATED      => "Your account has been updated successfully.\n\nRegards,\nGym Pro",
        self::PAYMENT_RECEIVED     => "Thank you! Your payment has been received.\n\nRegards,\nGym Pro",
        self::SUBSCRIPTION_EXPIRED => "Your Gym Pro subscription has expired. Please renew to continue.\n\nRegards,\nGym Pro",
        self::MEMBERSHIP_UPDATED   => "Your membership has been updated successfully.\n\nRegards,\nGym Pro",
        self::MEMBERSHIP_RENEWED   => "Your Gym Pro membership has been renewed successfully.\n\nRegards,\nGym Pro",
    ];
}
