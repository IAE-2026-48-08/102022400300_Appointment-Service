<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Appointment Service API',
    version: '1.0.0',
    description: 'API documentation for Service Jadwal Dokter'
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000/api',
    description: 'Local API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'ApiKeyAuth',
    type: 'apiKey',
    in: 'header',
    name: 'X-IAE-KEY'
)]
#[OA\Get(
    path: '/v1/appointments',
    summary: 'Mengambil daftar seluruh jadwal konsultasi dokter',
    tags: ['Appointments'],
    security: [['ApiKeyAuth' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Data appointments retrieved successfully'),
        new OA\Response(response: 401, description: 'Unauthorized. Invalid or missing API Key.')
    ]
)]
#[OA\Get(
    path: '/v1/appointments/{id}',
    summary: 'Mengambil detail jadwal konsultasi berdasarkan ID',
    tags: ['Appointments'],
    security: [['ApiKeyAuth' => []]],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID appointment',
            schema: new OA\Schema(type: 'integer')
        )
    ],
    responses: [
        new OA\Response(response: 200, description: 'Appointment detail retrieved successfully'),
        new OA\Response(response: 404, description: 'Appointment not found'),
        new OA\Response(response: 401, description: 'Unauthorized. Invalid or missing API Key.')
    ]
)]
#[OA\Post(
    path: '/v1/appointments',
    summary: 'Membuat jadwal konsultasi dan booking pasien baru',
    tags: ['Appointments'],
    security: [['ApiKeyAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['patient_name', 'doctor_name', 'specialization', 'appointment_date', 'appointment_time'],
            properties: [
                new OA\Property(property: 'patient_name', type: 'string', example: 'Siti Aminah'),
                new OA\Property(property: 'doctor_name', type: 'string', example: 'dr. Clara'),
                new OA\Property(property: 'specialization', type: 'string', example: 'Dokter Umum'),
                new OA\Property(property: 'appointment_date', type: 'string', format: 'date', example: '2026-06-11'),
                new OA\Property(property: 'appointment_time', type: 'string', example: '10:30'),
                new OA\Property(property: 'status', type: 'string', example: 'scheduled')
            ],
            type: 'object'
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Appointment created successfully'),
        new OA\Response(response: 401, description: 'Unauthorized. Invalid or missing API Key.'),
        new OA\Response(response: 422, description: 'Validation error')
    ]
)]
class SwaggerInfo
{
}