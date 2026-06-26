<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_get_appointments_with_valid_key()
    {
        $response = $this->withHeaders([
            'X-IAE-KEY' => '102022400300',
        ])->getJson('/api/v1/appointments');

        $response->assertStatus(200);
    }

    public function test_post_appointment_with_valid_key()
    {
        $response = $this->withHeaders([
            'X-IAE-KEY' => '102022400300',
        ])->post('/api/v1/appointments', [
            'patient_name' => 'Siti Aminah',
            'doctor_name' => 'dr. Clara',
            'specialization' => 'Dokter Umum',
            'appointment_date' => '2026-06-30',
            'appointment_time' => '10:00',
            'status' => 'scheduled'
        ]);

        $response->assertStatus(201);
    }


}
