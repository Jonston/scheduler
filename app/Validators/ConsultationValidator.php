<?php

namespace App\Validators;

use App\Exceptions\ConsultationException;
use App\Models\Consultation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ConsultationValidator
{

    protected $bookingBeforeHours;
    protected $cancellationBeforeHours;

    public function __construct()
    {
        $this->bookingBeforeHours = config('schedule.booking_before_hours');

        $this->cancellationBeforeHours = config('schedule.cancellation_before_hours');
    }

    public static function isDoctor(User $user)
    {
        if( ! $user->hasRole(Role::ROLE_DOCTOR))
            throw new ConsultationException("User is not doctor");

        return true;
    }

    public static function isPatient(User $user)
    {
        if( ! $user->hasRole(Role::ROLE_PATIENT))
            throw new ConsultationException("User is not patient");

        return true;
    }

    public static function isConsultationMember(Consultation $consultation, User $user)
    {
        //dd($consultation->toArray(), $user->toArray());

        if($consultation->doctor_id !== $user->id && $consultation->patient_id !== $user->id){
            throw new ConsultationException("The user is not a participant in the consultation");
        }

        return true;
    }

    public static function validateDoctorSchedule(User $doctor, Carbon $date)
    {
        $day = $date->format('l');
        $time = $date->format('H:i');

        $schedule = $doctor->weeklySchedule()->where('day', $day)->first();

        if( ! $schedule || ! $schedule->time()->where('time', $time)->first()){
            throw new ConsultationException("Invalid schedule date");
        }

        if($doctor->consultations()->where('booked_date', $date)->first()){
            throw new ConsultationException("At this time, there is already a consultation");
        }

        return true;
    }

    public static function validateCreationDate(Carbon $date, User $user)
    {
        // $now = Carbon::now(); //This is commented out for obvious reasons
        $now = config('schedule.now');

        // In this way, we reset the restriction for the doctor,
        // but at the same time we validate it if the date of the consultation is already in the past
        $bookingBeforeHours = $user->hasRole(Role::ROLE_DOCTOR)
            ? 0
            : config('schedule.booking_before_hours');

        if( ! $now->addHours($bookingBeforeHours)->lte($date))
            throw new ConsultationException("Invalid booked date");

        return true;
    }

    public static function validateCreation(User $doctor, User $patient, Carbon $date, User $creator)
    {
        static::isDoctor($doctor);
        static::isPatient($patient);
        static::validateDoctorSchedule($doctor, $date);
        static::validateCreationDate($date, $creator);

        return true;
    }

    public static function isConsultationExpired(Consultation $consultation)
    {
        // $now = Carbon::now(); //This is commented out for obvious reasons
        $now = config('schedule.now');

        if( ! $now->lte($consultation->booked_date))
            throw new ConsultationException("Confirmation is expired");

        return true;
    }

    public static function isConsultationConfirmedByPatient(Consultation $consultation, User $patient)
    {
        if(
            static::isPatient($patient)
            && $consultation->patient_id === $patient->id
            && $consultation->patient_confirmed_at
        ){
            throw new ConsultationException("Consultation already confirmed by patient");
        }

        return true;
    }

    public static function isConsultationConfirmedByDoctor(Consultation $consultation, User $doctor)
    {
        if(
            static::isDoctor($doctor)
            && $consultation->doctor_is === $doctor->id
            && $consultation->doctor_confirmed_at
        ){
            throw new ConsultationException("Consultation already confirmed by doctor");
        }

        return true;
    }

    public static function validateConfirmation(Consultation $consultation, User $confirmer)
    {
        static::isConsultationMember($consultation, $confirmer);
        static::isConsultationExpired($consultation, $confirmer);

        if($confirmer->hasRole(Role::ROLE_DOCTOR)){
            static::isConsultationConfirmedByDoctor($consultation, $confirmer);
        }

        if($confirmer->hasRole(Role::ROLE_PATIENT)){
            static::isConsultationConfirmedByPatient($consultation, $confirmer);
        }

        return true;
    }

    public static function isDoctorConsultation(Consultation $consultation, User $doctor)
    {
        if($consultation->doctor_id !== $doctor->id){
            throw new ConsultationException("The consultation is not related to this doctor");
        }

        return true;
    }

    public static function isPatientConsultation(Consultation $consultation, User $patient)
    {
        if($consultation->patient_id !== $patient->id){
            throw new ConsultationException("The consultation is not related to this patient");
        }

        return true;
    }

    public static function validateAlreadyConfirmed(Consultation $consultation)
    {
        if($consultation->status === Consultation::STATUS_CONFIRMED)
            throw new ConsultationException("Consultation already confirmed");

        return true;
    }

    public static function isConsultationAlreadyCanceled(Consultation $consultation)
    {
        if($consultation->status === Consultation::STATUS_CANCELED)
            throw new ConsultationException("Consultation already canceled");

        return true;
    }

    public static function validateCancellationDate(Consultation $consultation, User $canceler)
    {
        // $now = Carbon::now(); //This is commented out for obvious reasons
        $now = config('schedule.now');

        $timeBeforeCancel = $canceler->hasRole(Role::ROLE_DOCTOR)
            ? 0
            : config('schedule.cancellation_before_hours');

        if( ! $now->addHours($timeBeforeCancel)->lte($consultation->booked_date))
            throw new ConsultationException("Cannot cancel consultation");

        return true;
    }

    public static function validateCancellation(Consultation $consultation, User $canceler)
    {
        static::isConsultationMember($consultation, $canceler);
        static::isConsultationExpired($consultation);
        static::isConsultationAlreadyCanceled($consultation);
        static::validateCancellationDate($consultation, $canceler);

        return true;
    }



}
