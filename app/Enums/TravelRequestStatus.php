<?php

namespace App\Enums;

enum TravelRequestStatus: string
{
    case IN_REVIEW = 'in_review';
    case REVISION = 'revision';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';
    case SUBMITTED = 'submitted';
    case DRAFT = 'draft';

    /**
     * Get human-readable label for status
     */
    public function label(): string
    {
        return match($this) {
            self::IN_REVIEW => 'Sedang Review',
            self::REVISION => 'Revisi',
            self::REJECTED => 'Ditolak',
            self::COMPLETED => 'Disetujui',
            self::SUBMITTED => 'Diajukan',
            self::DRAFT => 'Draft'
        };
    }

    /**
     * Get CSS class for status badge
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::IN_REVIEW => 'bg-yellow-100 text-yellow-800',
            self::REVISION => 'bg-orange-100 text-orange-800',
            self::REJECTED => 'bg-red-100 text-red-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::SUBMITTED => 'bg-blue-100 text-blue-800',
            self::DRAFT => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Check if status is final (no further action needed)
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::REJECTED]);
    }

    /**
     * Check if status allows editing
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::REVISION, self::IN_REVIEW]);
    }

    /**
     * Get all statuses as array
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get statuses with labels for select options
     */
    public static function toSelectOptions(): array
    {
        $options = [];
        foreach (self::cases() as $status) {
            $options[$status->value] = $status->label();
        }
        return $options;
    }
} 