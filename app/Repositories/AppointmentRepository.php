<?php

namespace App\Repositories;

use App\Models\Appointment;

class AppointmentRepository
{
    protected $model;

    public function __construct(Appointment $model)
    {
        $this->model = $model;
    }


    public function all($filters = [])
    {
        $query = $this->model->with(['doctor', 'patient']);
        if (isset($filters['date'])) {
            $query->whereDate('appointment_time', $filters['date']);
        }
        if (isset($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }
        return $query->get();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $appointment = $this->find($id);
        $appointment->update($data);
        return $appointment;
    }

    public function delete($id)
    {
        $appointment = $this->find($id);
        $appointment->delete();
        return true;
    }

    public function checkConflict($doctorId, $appointmentTime, $excludeId = null)
    {
        $query = $this->model->where('doctor_id', $doctorId)
            ->where('appointment_time', $appointmentTime);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}