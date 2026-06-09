# Prompting Log - Tugas 2 IAE

Nama: Clara  
NIM: 102022400300  
Service: Appointment Service / Service Jadwal Dokter  
Resource Name: appointments  

## Ringkasan Penggunaan AI

AI digunakan untuk membantu proses pembuatan mini service Laravel untuk Tugas 2 Integrasi Aplikasi Enterprise. Bantuan yang digunakan meliputi perancangan endpoint REST API, pembuatan struktur response JSON, implementasi API Key, dokumentasi Swagger/OpenAPI, dan implementasi GraphQL.

## Daftar Prompt dan Hasil

### 1. Pembuatan Service Awal
Prompt:
> Membantu membuat service jadwal dokter dari awal menggunakan Laravel.

Hasil:
- Membuat project Laravel `appointment-service`.
- Membuat model `Appointment`.
- Membuat migration tabel `appointments`.
- Menggunakan database SQLite.

### 2. Pembuatan REST API
Prompt:
> Membantu membuat endpoint GET collection, GET detail, dan POST untuk resource appointments.

Hasil:
- GET `/api/v1/appointments`
- GET `/api/v1/appointments/{id}`
- POST `/api/v1/appointments`
- Response menggunakan format JSON wrapper dengan `status`, `message`, `data`, dan `meta`.

### 3. Implementasi API Key
Prompt:
> Membantu menambahkan security API Key menggunakan header X-IAE-KEY.

Hasil:
- Membuat middleware `CheckApiKey`.
- Header yang digunakan: `X-IAE-KEY`.
- Value API Key menggunakan NIM: `102022400300`.
- Request tanpa API Key menghasilkan response 401 Unauthorized.

### 4. Dokumentasi Swagger/OpenAPI
Prompt:
> Membantu menambahkan Swagger UI untuk dokumentasi API.

Hasil:
- Menginstall L5 Swagger.
- Membuat dokumentasi OpenAPI untuk endpoint appointments.
- Swagger UI dapat diakses melalui `/api/documentation`.
- Swagger sudah mendukung fitur Authorize menggunakan `X-IAE-KEY`.

### 5. Implementasi GraphQL
Prompt:
> Membantu membuat GraphQL query untuk mengambil data appointments.

Hasil:
- Menginstall Lighthouse GraphQL.
- Membuat schema GraphQL untuk type `Appointment`.
- Membuat query `appointments` dan `appointment(id)`.
- Query GraphQL berhasil mengambil data appointment.

## Kesimpulan

Dengan bantuan AI, service `Appointment Service` berhasil dibuat sesuai ketentuan Tugas 2, yaitu menyediakan REST API, response JSON konsisten, API Key security, Swagger documentation, dan GraphQL query.