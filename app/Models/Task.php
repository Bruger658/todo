<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['title', 'description', 'frequency', 'due_date', 'duration_days', 'realization_time'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'duration_days' => 'integer',
            'completed_at' => 'datetime',
            'realization_time' => 'string',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }     

    public function isOverdue(): bool
    {
        $scheduledAt = $this->scheduledAt();

        return ! $this->isCompleted()
            && $scheduledAt !== null
            && $scheduledAt->isPast();
    }

    public function scheduledAt(): ?Carbon
    {
        if ($this->due_date === null) {
            return null;
        }

        return $this->due_date
            ->copy()
            ->setTimeFromTimeString($this->realization_time !== null ? mb_substr($this->realization_time, 0, 5) : '23:59');
    }
}