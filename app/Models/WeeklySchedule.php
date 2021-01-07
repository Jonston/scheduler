<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    use HasFactory;

    protected $table = 'weekly_schedule';

    protected $fillable = [
        'day',
        'doctor_id'
    ];

    public function time()
    {
        return $this->hasMany(DailySchedule::class, 'day_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class);
    }
}
