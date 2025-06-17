# Medical Clinic API Documentation

## Project Setup Instructions

1. **System Requirements**:

   - PHP 8.2 or higher.
   - Composer.
   - XAMPP (for running Apache and MySQL locally).
   - MySQL database.

2. **Steps**:

   - Clone or download the project from the repository.
   - Open a terminal (Terminal) in the project directory.
   - Install dependencies using:

     ```bash
     composer install
     ```
   - Set up a database in XAMPP:
     - Open phpMyAdmin (via `http://localhost/phpmyadmin`).
     - Create a new database named `clinic_api` (or any name you prefer).
   - Configure the `.env` file by copying `.env.example` and editing the variables:
     - `DB_DATABASE=clinic_api`
     - `DB_USERNAME=root` (default in XAMPP)
     - `DB_PASSWORD=` (leave empty if no password is set)
   - Apply migrations to create the database tables:

     ```bash
     php artisan migrate
     ```
   - Seed the initial data: **This step is mandatory to add roles (roles) and basic data**:

     ```bash
     php artisan db:seed
     ```
   - Start the server:

     ```bash
     php artisan serve
     ```
   - Open a browser or Postman at `http://localhost:8000`.

## API Endpoints Details

### 1. Authentication

- **POST /api/login**
  - Description: Log in to obtain an access token.
  - Body:

    ```json
    {
        "email": "string",
        "password": "string"
    }
    ```
  - Response (200):

    ```json
    {
        "user": {...},
        "token": "string"
    }
    ```
- **POST /api/register**
  - Description: Register a new user.
  - Body:

    ```json
    {
        "name": "string",
        "email": "string",
        "password": "string",
        "role": "admin|doctor|receptionist|patient"
    }
    ```
  - Response (201):

    ```json
    {
        "user": {...},
        "token": "string"
    }
    ```

### 2. Appointment Management

- **GET /api/appointments**
  - Description: Retrieve a list of appointments based on role (admin: all appointments, doctor: doctor’s appointments, patient: patient’s appointments, receptionist: appointments without patient data).
  - Headers: `Authorization: Bearer <token>`
  - Response (200):

    ```json
    [
        {
            "id": integer,
            "doctor_name": "string",
            "patient_name": "string" (except for receptionist),
            "appointment_time": "datetime",
            "created_at": "datetime",
            "updated_at": "datetime"
        }
    ]
    ```
- **POST /api/appointments**
  - Description: Add a new appointment.
  - Headers: `Authorization: Bearer <token>`, `Content-Type: application/json`
  - Body:

    ```json
    {
        "doctor_id": integer,
        "appointment_time": "datetime"
    }
    ```
  - Response (201):

    ```json
    {
        "id": integer,
        "doctor_id": integer,
        "appointment_time": "datetime"
    }
    ```
- **PUT /api/appointments/{id}**
  - Description: Update an existing appointment.
  - Headers: `Authorization: Bearer <token>`, `Content-Type: application/json`
  - Body:

    ```json
    {
        "appointment_time": "datetime"
    }
    ```
  - Response (200):

    ```json
    {
        "id": integer,
        "doctor_id": integer,
        "appointment_time": "datetime"
    }
    ```
- **DELETE /api/appointments/{id}**
  - Description: Delete an appointment.
  - Headers: `Authorization: Bearer <token>`
  - Response (200):

    ```json
    {
        "message": "Appointment deleted"
    }
    ```

## Postman Usage Examples

### 1. Register Users

Before testing other endpoints, register one user for each role.

- **Method**: POST
- **URL**: `http://localhost:8000/api/register`
- **Headers**: `Content-Type: application/json`
- **Body** (for each role):
  - **Admin**:

    ```json
    {
        "name": "Admin User",
        "email": "admin@example.com",
        "password": "password123",
        "role": "admin"
    }
    ```
    - **Expected Response** (201): Token in the body.
  - **Doctor**:

    ```json
    {
        "name": "Dr. Mohammed Ali",
        "email": "doctor@example.com",
        "password": "password123",
        "role": "doctor"
    }
    ```
  - **Receptionist**:

    ```json
    {
        "name": "Recep User",
        "email": "recep@example.com",
        "password": "password123",
        "role": "receptionist"
    }
    ```
  - **Patient**:

    ```json
    {
        "name": "Patient 1",
        "email": "patient1@example.com",
        "password": "password123",
        "role": "patient"
    }
    ```
  - Copy the tokens from the responses for use in subsequent requests.

### 2. Login

- **Method**: POST
- **URL**: `http://localhost:8000/api/login`
- **Headers**: `Content-Type: application/json`
- **Body** (example for Admin):

  ```json
  {
      "email": "admin@example.com",
      "password": "password123"
  }
  ```
- **Expected Response**: Token in the body.

### 3. Add Appointment (Receptionist)

- **Method**: POST
- **URL**: `http://localhost:8000/api/appointments`
- **Headers**:
  - `Authorization: Bearer <recep_token>` (replace with token from receptionist registration).
  - `Content-Type: application/json`
- **Body**:

  ```json
  {
      "doctor_id": 2,  // Doctor ID from registration
      "appointment_time": "2025-06-18 12:00:00"
  }
  ```
- **Expected Response** (201):

  ```json
  {
      "id": 1,
      "doctor_id": 2,
      "appointment_time": "2025-06-18 12:00:00"
  }
  ```

### 4. Update Appointment (Receptionist)

- **Method**: PUT
- **URL**: `http://localhost:8000/api/appointments/1`
- **Headers**:
  - `Authorization: Bearer <recep_token>`
  - `Content-Type: application/json`
- **Body**:

  ```json
  {
      "appointment_time": "2025-06-18 12:30:00"
  }
  ```
- **Expected Response** (200):

  ```json
  {
      "id": 1,
      "doctor_id": 2,
      "appointment_time": "2025-06-18 12:30:00"
  }
  ```

### 5. Delete Appointment (Receptionist)

- **Method**: DELETE
- **URL**: `http://localhost:8000/api/appointments/1`
- **Headers**: `Authorization: Bearer <recep_token>`
- **Expected Response** (