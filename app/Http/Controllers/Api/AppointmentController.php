<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\SoapAuditService;
use App\Services\RabbitMqPublisherService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Appointment Service API",
 *     version="1.0.0",
 *     description="API documentation for Service Jadwal Dokter"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000/api",
 *     description="Local API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="ApiKeyAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-IAE-KEY"
 * )
 */
class AppointmentController extends Controller
{
        /**
     * @OA\Get(
     *     path="/v1/appointments",
     *     summary="Mengambil daftar seluruh jadwal konsultasi dokter",
     *     tags={"Appointments"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data appointments retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. Invalid or missing API Key."
     *     )
     * )
     */
    public function index()
    {
        $appointments = Appointment::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Data appointments retrieved successfully',
            'data' => $appointments,
            'meta' => [
                'service_name' => 'Appointment-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }

        /**
     * @OA\Get(
     *     path="/v1/appointments/{id}",
     *     summary="Mengambil detail jadwal konsultasi berdasarkan ID",
     *     tags={"Appointments"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID appointment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment detail retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. Invalid or missing API Key."
     *     )
     * )
     */
    public function show($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
                'errors' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment detail retrieved successfully',
            'data' => $appointment,
            'meta' => [
                'service_name' => 'Appointment-Service',
                'api_version' => 'v1'
            ]
        ], 200);
    }

        /**
     * @OA\Post(
     *     path="/v1/appointments",
     *     summary="Membuat jadwal konsultasi dan booking pasien baru",
     *     tags={"Appointments"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"patient_name","doctor_name","specialization","appointment_date","appointment_time"},
     *             @OA\Property(property="patient_name", type="string", example="Siti Aminah"),
     *             @OA\Property(property="doctor_name", type="string", example="dr. Clara"),
     *             @OA\Property(property="specialization", type="string", example="Dokter Umum"),
     *             @OA\Property(property="appointment_date", type="string", format="date", example="2026-06-11"),
     *             @OA\Property(property="appointment_time", type="string", example="10:30"),
     *             @OA\Property(property="status", type="string", example="scheduled")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Appointment created successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. Invalid or missing API Key."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
  public function store(
    Request $request,
    SoapAuditService $soapAuditService,
    RabbitMqPublisherService $rabbitMqPublisherService
)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'doctor_name' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'status' => 'nullable|string|max:255',
        ]);

        $appointment = Appointment::create($validated);

        $authHeader = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        $soapAudit = $soapAuditService->sendAppointmentAudit(
            $appointment->toArray(),
            $token
        );

        $appointment->update([
            'soap_receipt_number' => $soapAudit['receipt_number'],
            'soap_audit_response' => $soapAudit['body'],
        ]);
        $rabbitMq = $rabbitMqPublisherService->publishAppointmentCreated(
        $appointment->fresh()->toArray(),
            $token
);

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment created successfully',
            'data' => $appointment->fresh(),
'integration' => [
    'soap_audit' => [
        'success' => $soapAudit['success'],
        'status_code' => $soapAudit['status_code'],
        'receipt_number' => $soapAudit['receipt_number'],
    ],
    'rabbitmq_publish' => [
        'success' => $rabbitMq['success'],
        'status_code' => $rabbitMq['status_code'],
        'response' => $rabbitMq['body'],
    ]
],
            'meta' => [
                'service_name' => 'Appointment-Service',
                'api_version' => 'v1'
            ]
        ], 201);
    }
}