<?php
namespace App\Http\Controllers;

use App\Services\AppointmentService;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['date', 'doctor_id']);
        $appointments = $this->appointmentService->getAppointments($filters);
        return response()->json($appointments);
    }

   public function store(StoreAppointmentRequest $request)
    {
        try {
            $appointment = $this->appointmentService->createAppointment($request->validated());
            return response()->json($appointment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $appointment = $this->appointmentService->getAppointment($id);
        return response()->json($appointment);
    }

    public function update(UpdateAppointmentRequest $request, $id)
    {
        try {
            $appointment = $this->appointmentService->updateAppointment($id, $request->validated());
            return response()->json($appointment, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

    public function destroy($id)
    {
        try {
            $this->appointmentService->deleteAppointment($id);
            return response()->json(['message' => 'Appointment deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 404);
        }
    }
}