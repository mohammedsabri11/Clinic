<?php 
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        $user = $this->user();
        return $user && in_array($user->roles()->first()->name, ['admin', 'receptionist']);
    }

    public function rules()
    {
        return [
            'patient_id' => 'sometimes|exists:users,id',
            'doctor_id' => 'sometimes|exists:users,id',
            'appointment_time' => 'sometimes|date',
        ];
    }
}