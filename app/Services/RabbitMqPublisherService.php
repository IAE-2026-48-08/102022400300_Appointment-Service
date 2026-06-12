<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RabbitMqPublisherService
{
    public function publishAppointmentCreated(array $appointment, string $token): array
    {
        $url = env('IAE_RABBITMQ_PUBLISH_URL');

        $payload = [
    'team_id' => 'TEAM-13',
    'service_name' => 'Appointment-Service',
    'event' => 'appointment.created',
    'routing_key' => 'appointment.created',
    'message' => [
        'appointment_id' => $appointment['id'],
        'patient_name' => $appointment['patient_name'],
        'doctor_name' => $appointment['doctor_name'],
        'specialization' => $appointment['specialization'],
        'appointment_date' => $appointment['appointment_date'],
        'appointment_time' => $appointment['appointment_time'],
        'status' => $appointment['status'] ?? 'scheduled',
        'soap_receipt_number' => $appointment['soap_receipt_number'] ?? null,
    ],
];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-API-KEY' => env('IAE_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        return [
            'success' => $response->successful(),
            'status_code' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }
}