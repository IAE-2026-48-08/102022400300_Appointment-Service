<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\RabbitMqPublisherService;
use App\Services\SoapAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Throwable;

/**
 * @OA\Info(
 *     title="Appointment Service API",
 *     version="1.0.0",
 *     description="API documentation for Appointment Service"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local Docker API Server"
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
     *     summary="Mengambil seluruh data appointment",
     *     tags={"Appointments"},
     *     security={{"ApiKeyAuth":{}}},
     *
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
                'api_version' => 'v1',
            ],
        ], 200);
    }

    #[\OpenApi\Attributes\Get(
        path: '/v1/appointments/health',
        operationId: 'appointmentHealth',
        summary: 'Memeriksa status Appointment Service',
        tags: ['Appointments'],
        security: [['ApiKeyAuth' => []]],
        responses: [
            new \OpenApi\Attributes\Response(
                response: 200,
                description: 'Appointment Service is running'
            ),
            new \OpenApi\Attributes\Response(
                response: 401,
                description: 'Unauthorized. Invalid or missing API Key.'
            ),
        ]
    )]
    public function health()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Appointment Service is running',
            'data' => [
                'service' => 'Appointment-Service',
                'condition' => 'healthy',
            ],
            'meta' => [
                'service_name' => 'Appointment-Service',
                'api_version' => 'v1',
            ],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/v1/appointments/{id}",
     *     summary="Mengambil detail appointment berdasarkan ID",
     *     tags={"Appointments"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID appointment",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
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
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment detail retrieved successfully',
            'data' => $appointment,
            'meta' => [
                'service_name' => 'Appointment-Service',
                'api_version' => 'v1',
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/v1/appointments",
     *     summary="Membuat appointment baru",
     *     tags={"Appointments"},
     *     security={{"ApiKeyAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={
     *                 "patient_name",
     *                 "doctor_name",
     *                 "specialization",
     *                 "appointment_date",
     *                 "appointment_time"
     *             },
     *
     *             @OA\Property(
     *                 property="patient_name",
     *                 type="string",
     *                 example="Siti Aminah"
     *             ),
     *             @OA\Property(
     *                 property="doctor_name",
     *                 type="string",
     *                 example="dr. Clara"
     *             ),
     *             @OA\Property(
     *                 property="specialization",
     *                 type="string",
     *                 example="Dokter Umum"
     *             ),
     *             @OA\Property(
     *                 property="appointment_date",
     *                 type="string",
     *                 format="date",
     *                 example="2026-06-30"
     *             ),
     *             @OA\Property(
     *                 property="appointment_time",
     *                 type="string",
     *                 example="10:00"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="scheduled"
     *             )
     *         )
     *     ),
     *
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
    ) {
        $validator = Validator::make($request->all(), [
            'patient_name' => 'required|string|max:255',
            'doctor_name' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'status' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $validated['status'] = $validated['status'] ?? 'scheduled';

        $appointment = Appointment::create($validated);

        $authHeader = (string) $request->header('Authorization', '');
        $token = null;

        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = trim(substr($authHeader, 7));
        }

        $soapAudit = [
            'success' => false,
            'status_code' => null,
            'body' => null,
            'receipt_number' => null,
            'skipped' => true,
            'message' => 'SOAP integration skipped because token or URL is unavailable.',
        ];

        $rabbitMq = [
            'success' => false,
            'status_code' => null,
            'body' => null,
            'skipped' => true,
            'message' => 'RabbitMQ integration skipped because token or URL is unavailable.',
        ];

        $soapUrl = env('IAE_SOAP_AUDIT_URL');
        $rabbitMqUrl = env('IAE_RABBITMQ_PUBLISH_URL');

        if ($token && $soapUrl) {
            try {
                $soapAudit = $soapAuditService->sendAppointmentAudit(
                    $appointment->toArray(),
                    $token
                );

                $soapAudit['skipped'] = false;
                $soapAudit['message'] = null;

                $appointment->update([
                    'soap_receipt_number' => $soapAudit['receipt_number'] ?? null,
                    'soap_audit_response' => $soapAudit['body'] ?? null,
                ]);
            } catch (Throwable $exception) {
                report($exception);

                $soapAudit = [
                    'success' => false,
                    'status_code' => 500,
                    'body' => $exception->getMessage(),
                    'receipt_number' => null,
                    'skipped' => false,
                    'message' => 'SOAP integration failed, but appointment was created.',
                ];
            }
        }

        if ($token && $rabbitMqUrl) {
            try {
                $rabbitMq = $rabbitMqPublisherService
                    ->publishAppointmentCreated(
                        $appointment->fresh()->toArray(),
                        $token
                    );

                $rabbitMq['skipped'] = false;
                $rabbitMq['message'] = null;
            } catch (Throwable $exception) {
                report($exception);

                $rabbitMq = [
                    'success' => false,
                    'status_code' => 500,
                    'body' => $exception->getMessage(),
                    'skipped' => false,
                    'message' => 'RabbitMQ integration failed, but appointment was created.',
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment created successfully',
            'data' => $appointment->fresh(),
            'integration' => [
                'soap_audit' => [
                    'success' => $soapAudit['success'],
                    'status_code' => $soapAudit['status_code'],
                    'receipt_number' => $soapAudit['receipt_number'] ?? null,
                    'skipped' => $soapAudit['skipped'],
                    'message' => $soapAudit['message'] ?? null,
                ],
                'rabbitmq_publish' => [
                    'success' => $rabbitMq['success'],
                    'status_code' => $rabbitMq['status_code'],
                    'response' => $rabbitMq['body'],
                    'skipped' => $rabbitMq['skipped'],
                    'message' => $rabbitMq['message'] ?? null,
                ],
            ],
            'meta' => [
                'service_name' => 'Appointment-Service',
                'api_version' => 'v1',
            ],
        ], 201);
    }
}