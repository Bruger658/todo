<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['title', 'description', 'frequency', 'due_date', 'realization_time'])]
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
            'completed_at' => 'datetime',
            'realization_time' => 'string',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }
    public function reminderAt(?Carbon $now = null): ?Carbon
    {
        if ($this->realization_time === null) {
            return null;
        }

        return ($this->due_date ?? $now ?? now())->copy()->setTimeFromTimeString($this->realization_time);
    }

    public function shouldShowReminder(Carbon $now): bool
    {
        $reminderAt = $this->reminderAt($now);

        if ($this->isCompleted() || $reminderAt === null) {
            return false;
        }

        return $reminderAt->between($now, $now->copy()->addHour());
    }
}