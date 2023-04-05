<?php

namespace App\Service\Enum;

enum CommentState: string
{
    case SUBMITTED = 'submitted';
    case SPAM = 'spam';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::SUBMITTED => 'info',
            self::SPAM      => 'danger',
            self::PUBLISHED => 'success'
        };
    }
}
