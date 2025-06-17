<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        $user = $this->user();
        return $user && in_array($user->roles()->first()->name, ['admin', 'receptionist', 'patient']);
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_time' => 'required|date',
        ];
    }
}