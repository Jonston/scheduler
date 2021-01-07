<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    const STATUS_CREATED = 1;
    const STATUS_CONFIRMED = 2;
    const STATUS_CANCELED = 3;
    const STATUS_CLOSED = 4;

    const WEEKLY_SCHEDULE = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
    ];

    const DAILY_SCHEDULE = [
        "00:00", "00:30", "01:00", "01:30", "02:00", "02:30", "03:00", "03:30",
        "04:00", "04:30", "05:00", "05:30", "06:00", "06:30", "07:00", "07:30",
        "08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30",
        "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30",
        "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", "19:00", "19:30",
        "20:00", "20:30", "21:00", "21:30", "22:00", "22:30", "23:00", "23:30",
    ];

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'booked_date',
        'doctor_confirmed_at',
        'patient_confirmed_at',
        'status'
    ];

    protected $dates = [
        'booked_date',
        'doctor_confirmed_at',
        'patient_confirmed_at',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }
}
