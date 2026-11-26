<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanModule extends Model
{
    protected $guarded = [];

    /** Icons offered in the icon picker (Bootstrap Icons). Any other "bi-*" name can be typed in. */
    public const ICONS = [
        'bi-puzzle', 'bi-box-seam', 'bi-graph-up', 'bi-people', 'bi-geo-alt', 'bi-fuel-pump',
        'bi-shield-check', 'bi-bell', 'bi-bar-chart', 'bi-cloud', 'bi-credit-card', 'bi-gear',
        'bi-clipboard-data', 'bi-envelope', 'bi-phone', 'bi-qr-code', 'bi-truck', 'bi-receipt',
        'bi-calendar-check', 'bi-boxes', 'bi-building', 'bi-wallet2', 'bi-stars', 'bi-whatsapp',
        'bi-chat-dots', 'bi-file-earmark-text', 'bi-person-badge', 'bi-patch-check',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_system' => 'boolean'];
    }

    /** Names of the plans that currently include this module. */
    public function plansUsing()
    {
        return Plan::whereJsonContains('features', $this->key)->orderBy('sort_order')->pluck('name');
    }

    public function isInUse(): bool
    {
        return Plan::whereJsonContains('features', $this->key)->exists();
    }
}