<?php
namespace App\Services;

use App\Repositories\AppointmentRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class AppointmentService
{
    protected $appointmentRepository;
    protected $userRepository;

    public function __construct(AppointmentRepository $appointmentRepository, UserRepository $userRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
        $this->userRepository = $userRepository;
    }


public function getAppointments(array $filters = [])
{
    $user = Auth::user();
    $appointments = $this->appointmentRepository->all($filters);

    if ($user->hasRole('doctor')) {
        $appointments = $appointments->where('doctor_id', $user->id);
        return $appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'patient_name' => $appointment->patient->name ?? 'Unknown patient',
                'appointment_time' => $appointment->appointment_time,
            ];
        });
    } elseif ($user->hasRole('patient')) {
        $appointments = $appointments->where('patient_id', $user->id);
         return $appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'doctor_name' => $appointment->doctor->name ?? 'Unknown Doctor',
                'appointment_time' => $appointment->appointment_time,
            ];
        });
    }
    elseif ($user->hasRole('receptionist')) {
        return $appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'doctor_name' => $appointment->doctor->name ?? 'Unknown Doctor',
                'appointment_time' => $appointment->appointment_time,
            ];
        });
    }


    return $appointments->map(function ($appointment) {
        return [
            'id' => $appointment->id,
            'doctor_name' => $appointment->doctor->name ?? 'Unknown Doctor',
            'patient_name' => $appointment->patient->name ?? 'Unknown Patient',
            'appointment_time' => $appointment->appointment_time,
            'created_at' => $appointment->created_at,
            'updated_at' => $appointment->updated_at,
        ];
    });
}

    public function getAppointment($id)
    {
        $appointment = $this->appointmentRepository->find($id);
        $user = Auth::user();

        if ($user->hasRole('doctor') && $appointment->doctor_id !== $user->id) {
            throw new \Exception('Unauthorized', 403);
        }
        if ($user->hasRole('patient') && $appointment->patient_id !== $user->id) {
            throw new \Exception('Unauthorized', 403);
        }

        if ($user->hasRole('receptionist')) {
            return [
                'id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_time' => $appointment->appointment_time,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        }

        return $appointment;
    }

    public function createAppointment(array $data)
    {
        $conflict = $this->appointmentRepository->checkConflict($data['doctor_id'], $data['appointment_time']);
        
        if ($conflict) {
            throw new \Exception('Time slot already booked', 422);
        }
        return $this->appointmentRepository->create($data);
    }

    public function updateAppointment($id, array $data)
    {
        $appointment = $this->appointmentRepository->find($id);

        if (isset($data['appointment_time']) && isset($data['doctor_id'])) {
            $conflict = $this->appointmentRepository->checkConflict($data['doctor_id'], $data['appointment_time'], $id);
            if ($conflict) {
                throw new \Exception('Time slot already booked', 422);
            }
        }

        return $this->appointmentRepository->update($id, $data);
    }

    public function deleteAppointment($id)
    {
        $appointment = $this->appointmentRepository->find($id);
        if (!$appointment) {
            throw new \Exception('Appointment not found', 404);
        }
        return $this->appointmentRepository->delete($id);
    }
}