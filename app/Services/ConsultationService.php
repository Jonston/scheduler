<?php

namespace App\Services;

use App\Exceptions\ConsultationException;
use App\Models\Consultation;
use App\Models\Role;
use App\Models\User;
use App\Validators\ConsultationValidator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ConsultationService
{

    protected $bookingBeforeHours;
    protected $cancellationBeforeHours;

    public function __construct()
    {
        $this->bookingBeforeHours = config('schedule.booking_before_hours');

        $this->cancellationBeforeHours = config('schedule.cancellation_before_hours');
    }

    public function create(User $doctor, User $patient, Carbon $date)
    {
        ConsultationValidator::validateCreation($doctor, $patient, $date, Auth::user());

        return Consultation::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'booked_date' => $date,
            'status' => Consultation::STATUS_CREATED
        ]);
    }

    public function confirm(Consultation $consultation)
    {
        $user = Auth::user();

        ConsultationValidator::validateConfirmation($consultation, $user);

        $now = config('schedule.now');

        if($user->hasRole(Role::ROLE_DOCTOR)){
            $consultation->doctor_confirmed_at = $now;
        }else{
            $consultation->patient_confirmed_at = $now;
        }

        if($consultation->doctor_confirmed_at && $consultation->patient_confirmed_at){
            $consultation->status = Consultation::STATUS_CONFIRMED;
        }

        return $consultation->save();
    }

    public static function cancel(Consultation $consultation)
    {
        $user = Auth::user();

        ConsultationValidator::validateCancellation($consultation, $user);

        $consultation->status = Consultation::STATUS_CANCELED;
        return $consultation->save();
    }

}
