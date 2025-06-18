<?php

namespace App\Services;

use App\Repositories\AppointmentRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

/**
 * Service class for managing appointment-related operations.
 */
class AppointmentService
{
    /** @var AppointmentRepository */
    protected $appointmentRepository;

    /** @var UserRepository */
    protected $userRepository;

    /**
     * AppointmentService constructor.
     *
     * @param AppointmentRepository $appointmentRepository
     * @param UserRepository $userRepository
     */
    public function __construct(AppointmentRepository $appointmentRepository, UserRepository $userRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Retrieve appointments based on user role and filters.
     *
     * @param array $filters Optional filters (e.g., date, doctor_id)
     * @return Collection
     */
    public function getAppointments(array $filters = []): Collection
    {
        $user = Auth::user();
        $appointments = $this->appointmentRepository->all($filters);

        $role = $user->role ?? 'guest'; // Default to 'guest' if role is null
        return $this->filterAppointmentsByRole($appointments, $role);
    }

    /**
     * Retrieve a single appointment with role-based access control.
     *
     * @param int $id
     * @return array|object
     * @throws \Exception
     */
    public function getAppointment(int $id)
    {
        $appointment = $this->appointmentRepository->find($id);
        $user = Auth::user();

        $this->authorizeAppointmentAccess($appointment, $user);

        return $this->formatAppointmentResponse($appointment, $user->role ?? 'guest');
    }

    /**
     * Create a new appointment with conflict checking.
     *
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function createAppointment(array $data)
    {
        $this->checkTimeConflict($data['doctor_id'], $data['appointment_time']);

        return $this->appointmentRepository->create($data);
    }

    /**
     * Update an existing appointment with conflict checking.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function updateAppointment(int $id, array $data)
    {
        $appointment = $this->appointmentRepository->find($id);

        if (!$appointment) {
            throw new \Exception('Appointment not found', 404);
        }

        if (isset($data['appointment_time']) && isset($data['doctor_id'])) {
            $this->checkTimeConflict($data['doctor_id'], $data['appointment_time'], $id);
        }

        return $this->appointmentRepository->update($id, $data);
    }

    /**
     * Delete an appointment.
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function deleteAppointment(int $id): array
    {
        $appointment = $this->appointmentRepository->find($id);

        if (!$appointment) {
            throw new \Exception('Appointment not found', 404);
        }

        $this->appointmentRepository->delete($id);
        return ['message' => 'Appointment deleted'];
    }

    /**
     * Filter appointments based on user role.
     *
     * @param Collection $appointments
     * @param string $role
     * @return Collection
     */
    private function filterAppointmentsByRole(Collection $appointments, string $role): Collection
    {
        switch (strtolower($role)) {
            case 'doctor':
                $appointments = $appointments->where('doctor_id', Auth::user()->id);
                return $appointments->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'patient_name' => $appointment->patient->name ?? 'Unknown Patient',
                        'appointment_time' => $appointment->appointment_time,
                    ];
                });
            case 'patient':
                $appointments = $appointments->where('patient_id', Auth::user()->id);
                return $appointments->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'doctor_name' => $appointment->doctor->name ?? 'Unknown Doctor',
                        'appointment_time' => $appointment->appointment_time,
                    ];
                });
            case 'receptionist':
                return $appointments->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'doctor_name' => $appointment->doctor->name ?? 'Unknown Doctor',
                        'appointment_time' => $appointment->appointment_time,
                    ];
                });
            default:
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
    }

    /**
     * Authorize user access to an appointment.
     *
     * @param object $appointment
     * @param object $user
     * @throws \Exception
     */
    private function authorizeAppointmentAccess($appointment, $user)
    {
        if (!$appointment) {
            throw new \Exception('Appointment not found', 404);
        }

        if ($user->role === 'doctor' && $appointment->doctor_id !== $user->id) {
            throw new \Exception('Unauthorized', 403);
        }

        if ($user->role === 'patient' && $appointment->patient_id !== $user->id) {
            throw new \Exception('Unauthorized', 403);
        }
    }

    /**
     * Format appointment response based on user role.
     *
     * @param object $appointment
     * @param string $role
     * @return array
     */
    private function formatAppointmentResponse($appointment, string $role): array
    {
        if (strtolower($role) === 'receptionist') {
            return [
                'id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_time' => $appointment->appointment_time,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        }

        return $appointment->toArray();
    }

    /**
     * Check for time slot conflicts.
     *
     * @param int $doctorId
     * @param string $appointmentTime
     * @param int|null $excludeId
     * @throws \Exception
     */
    private function checkTimeConflict(int $doctorId, string $appointmentTime, int $excludeId = null)
    {
        $conflict = $this->appointmentRepository->checkConflict($doctorId, $appointmentTime, $excludeId);

        if ($conflict) {
            throw new \Exception('Time slot already booked', 422);
        }
    }
}