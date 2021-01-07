<?php

namespace Tests\Unit;

use App\Exceptions\ConsultationException;
use App\Models\Consultation;
use App\Models\User;
use App\Services\ConsultationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ConsultationTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        DB::table('consultations')->truncate();

        Config::set('schedule.now', Carbon::parse('2021-01-03 10:00:00'));
    }

    public function testDoctorCanCreateConsultationWithoutPreOrder()
    {
        $doctor = User::find(1); //Doctor
        $patient = User::find(2); //Patient
        $date = Carbon::parse('2021-01-04 09:00:00');
        Auth::login($doctor); //Login as doctor

        $service = new ConsultationService();
        $consultation = $service->create($doctor, $patient, $date);

        $this->assertEquals($consultation->id, 1);
    }

    public function testPatientCannotCreateConsultationWithoutPreOrder()
    {
        $doctor = User::find(1); //Doctor
        $patient = User::find(2); //Patient
        $date = Carbon::parse('2021-01-04 09:00:00');
        Auth::login($patient); //Login as doctor
        $service = new ConsultationService();

        try{
            $service->create($doctor, $patient, $date);
        }catch (ConsultationException $e){
            $this->assertEquals($e->getMessage(), 'Invalid booked date');
        }
    }

    public function testUserCannotCreateConsultationInIncorrectScheduleDate()
    {
        $doctor = User::find(1); //Doctor
        $patient = User::find(2); //Patient
        $date = Carbon::parse('2021-01-04 24:00:00'); //Incorrect date
        Auth::login($patient); //Login as doctor
        $service = new ConsultationService();

        try{
            $service->create($doctor, $patient, $date);
        }catch (ConsultationException $e){
            $this->assertEquals($e->getMessage(), 'Invalid schedule date');
        }
    }

    public function testPatientCannotCreateConsultationWithPatient()
    {
        $doctor = User::find(2); //Patient
        $patient = User::find(3); //Patient
        $date = Carbon::parse('2021-01-04 10:00:00');
        Auth::login($patient); //Login as doctor
        $service = new ConsultationService();

        try{
            $service->create($doctor, $patient, $date);
        }catch (ConsultationException $e){
            $this->assertEquals($e->getMessage(), 'User is not doctor');
        }
    }

    public function testDoctorAndPatientConsultationConfirm()
    {
        $doctor = User::find(1); //Doctor
        $patient = User::find(2); //Patient
        $date = Carbon::parse('2021-01-04 09:00:00');
        Auth::login($doctor); //Login as doctor

        $service = new ConsultationService();
        $consultation = $service->create($doctor, $patient, $date);
        $confirm = $service->confirm($consultation);

        $this->assertTrue($confirm);

        Auth::login($patient); //Login as patient
        $confirm = $service->confirm($consultation);
        $this->assertTrue($confirm);
    }

    public function testDoctorCannotConfirmExpiredConsultation()
    {
        $doctor = User::find(1); //Doctor
        $patient = User::find(2); //Patient
        $date = Carbon::parse('2021-01-04 09:00:00');
        Auth::login($doctor); //Login as doctor

        $service = new ConsultationService();
        $consultation = $service->create($doctor, $patient, $date);

        Config::set('schedule.now', Carbon::parse('2021-01-04 10:00:00'));

        try{
            $service->confirm($consultation);
        }catch (ConsultationException $e){
            $this->assertEquals($e->getMessage(), 'Confirmation is expired');
        }
    }

    public function testDoctorCanCancelConsultation()
    {
        $doctor = User::find(1); //Doctor
        $patient = User::find(2); //Patient
        $date = Carbon::parse('2021-01-04 09:00:00');
        Auth::login($doctor); //Login as doctor

        $service = new ConsultationService();
        $consultation = $service->create($doctor, $patient, $date);
        $cancel = $service->cancel($consultation);

        $this->assertTrue($cancel);
    }

    public function testDoctorCanCancelConsultationWithoutRestrictions()
    {
        $doctor = User::find(1); //Doctor
        $patient = User::find(2); //Patient
        $date = Carbon::parse('2021-01-04 09:00:00');
        Auth::login($doctor); //Login as doctor

        Config::set('schedule.now', Carbon::parse('2021-01-04 8:30:00'));

        $service = new ConsultationService();
        $consultation = $service->create($doctor, $patient, $date);
        $cancel = $service->cancel($consultation);

        $this->assertTrue($cancel);
    }

    public function testPatientCannotCancelConsultationWithoutRestrictions()
    {
        $doctor = User::find(1); //Doctor
        $patient = User::find(2); //Patient
        $date = Carbon::parse('2021-01-04 09:00:00');
        Auth::login($doctor); //Login as doctor

        $service = new ConsultationService();
        $consultation = $service->create($doctor, $patient, $date);

        Config::set('schedule.now', Carbon::parse('2021-01-04 8:30:00'));
        Auth::login($patient); //Login as doctor

        try{
            $service->cancel($consultation);
        }catch (ConsultationException $e){
            $this->assertEquals($e->getMessage(), 'Cannot cancel consultation');
        }
    }
}
