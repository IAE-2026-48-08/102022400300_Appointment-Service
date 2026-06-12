# Appointment Service

Nama: Faiza Clara Vimanda <br>
NIM: 102022400300 <br>
Mata Kuliah: Integrasi Aplikasi Enterprise <br>
Service: Appointment Service <br>

## Deskripsi Service

Appointment Service adalah layanan berbasis Laravel yang digunakan untuk mengelola jadwal konsultasi antara pasien dan dokter. Service ini menyediakan fitur untuk melihat daftar appointment, melihat detail appointment, dan membuat appointment baru.

## Endpoint Utama

Endpoint utama pada service ini adalah:

* GET `/api/v1/appointments`
* GET `/api/v1/appointments/{id}`
* POST `/api/v1/appointments`

## Transaksi Kritis

Transaksi kritis yang dipilih adalah:

POST `/api/v1/appointments`

Transaksi ini dipilih karena digunakan untuk membuat data appointment baru. Proses ini bersifat state-changing karena menambahkan data baru ke database `appointments`.

## Integrasi Tugas 3

Pada Tugas 3, Appointment Service sudah diintegrasikan dengan layanan terpusat dosen, yaitu:

1. SSO/JWT untuk autentikasi token.
2. SOAP Audit untuk mencatat transaksi kritis dan menyimpan ReceiptNumber.
3. RabbitMQ Publisher untuk mengirim event bisnis `appointment.created`.

## File Penting

Beberapa file penting pada project ini adalah:

* `analisis_tugas_3.md`
* `diagram/sequence_diagram_tugas3_appointment_service.png.jpg`
* `routes/api.php`
* `app/Http/Controllers/Api/AppointmentController.php`
* `app/Http/Middleware/CheckApiKey.php`
* `app/Http/Middleware/VerifySsoJwt.php`
* `app/Services/SoapAuditService.php`
* `app/Services/RabbitMqPublisherService.php`
* `app/Models/Appointment.php`
* `app/Models/LocalRole.php`

## Cara Menjalankan Project

Jalankan server Laravel dengan command:

```bash
php artisan serve
```

Base URL lokal:

```txt
http://127.0.0.1:8000
```

## Contoh Header Request

Untuk endpoint yang membutuhkan API Key dan JWT, gunakan header:

```txt
X-IAE-KEY: 102022400300
Authorization: Bearer <token>
Content-Type: application/json
```

## Status Implementasi

* REST API Appointment Service selesai.
* API Key Security selesai.
* Swagger/OpenAPI selesai.
* GraphQL query selesai.
* SSO/JWT selesai.
* SOAP Audit selesai.
* RabbitMQ Publisher selesai.
* Sequence diagram dan analisis Tugas 3 selesai.
