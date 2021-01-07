<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySchedule extends Model
{
    use HasFactory;

    protected $table = 'daily_schedule';

    protected $fillable = [
        'time',
        'day_id',
        'doctor_id'
    ];

    public function day()
    {
        return $this->belongsTo(WeeklySchedule::class, 'day_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
