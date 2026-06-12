<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_name',
        'doctor_name',
        'specialization',
        'appointment_date',
        'appointment_time',
        'status',
        'soap_receipt_number',
        'soap_audit_response',
    ];
}