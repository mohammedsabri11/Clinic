# Medical Clinic API Documentation

## Project Overview

This API delivers a robust solution for managing a medical clinic's appointment system, utilizing a **Repository-Service-Controller (RSC)** architectural pattern integrated with Laravel’s MVC framework. Authentication is secured via **Laravel Sanctum**, with role-based access control for admin, doctor, receptionist, and patient roles.

## Setup and Installation

### System Requirements

- PHP 8.2 or higher
- Composer
- XAMPP (for local Apache and MySQL services)
- MySQL database

### Installation Steps

1. Clone or download the project repository.
2. Navigate to the project directory via terminal.
3. Install dependencies:

   ```bash
   composer install
   ```
4. Configure the database in XAMPP:
   - Access phpMyAdmin at `http://localhost/phpmyadmin`.
   - Create a new database named `clinic_api` (or a custom name).
5. Set up the `.env` file by duplicating `.env.example` and updating:
   - `DB_DATABASE=clinic_api`
   - `DB_USERNAME=root` (XAMPP default)
   - `DB_PASSWORD=` (leave empty unless a password is set)
6. Execute migrations to initialize database tables:

   ```bash
   php artisan migrate
   ```
7. Seed initial data (mandatory for role setup):

   ```bash
   php artisan db:seed
   ```
8. Launch the development server:

   ```bash
   php artisan serve
   ```
9. Access the API via browser or Postman at `http://localhost:8000`.

## API Endpoints

### 1. Authentication

- **POST /api/login**
  - **Purpose**: Authenticate and retrieve an access token.
  - **Request Body**:

    ```json
    {
        "email": "string",
        "password": "string"
    }
    ```
  - **Response (200)**:

    ```json
    {
        "user": {...},
        "token": "string"
    }
    ```
- **POST /api/register**
  - **Purpose**: Register a new user with a specified role.
  - **Request Body**:

    ```json
    {
        "name": "string",
        "email": "string",
        "password": "string",
        "role": "admin|doctor|receptionist|patient"
    }
    ```
  - **Response (201)**:

    ```json
    {
        "user": {...},
        "token": "string"
    }
    ```

### 2. Appointment Management

- **GET /api/appointments**
  - **Purpose**: Retrieve appointments filtered by user role (admin: all, doctor: own, patient: own, receptionist: all details except patient name).
  - **Headers**: `Authorization: Bearer <token>`
  - **Query Parameters**: `?date=YYYY-MM-DD&doctor_id=integer` (optional filters)
  - **Response (200)**:
    - For admin, doctor, patient:
      ```json
      [
          {
              "id": integer,
              "doctor_name": "string",
              "patient_name": "string",
              "appointment_time": "datetime",
              "created_at": "datetime",
              "updated_at": "datetime"
          }
      ]
      ```
    - For receptionist:
      ```json
      [
          {
              "id": integer,
              "doctor_name": "string",
              "appointment_time": "datetime",
              "created_at": "datetime",
              "updated_at": "datetime"
          }
      ]
      ```
- **POST /api/appointments**
  - **Purpose**: Create a new appointment.
  - **Headers**: `Authorization: Bearer <token>`, `Content-Type: application/json`
  - **Request Body**:

    ```json
    {
        "doctor_id": integer,
        "appointment_time": "datetime"
    }
    ```
  - **Response (201)**:

    ```json
    {
        "id": integer,
        "doctor_id": integer,
        "appointment_time": "datetime"
    }
    ```
- **PUT /api/appointments/{id}**
  - **Purpose**: Update an existing appointment’s time.
  - **Headers**: `Authorization: Bearer <token>`, `Content-Type: application/json`
  - **Request Body**:

    ```json
    {
        "appointment_time": "datetime"
    }
    ```
  - **Response (200)**:

    ```json
    {
        "id": integer,
        "doctor_id": integer,
        "appointment_time": "datetime"
    }
    ```
  - **Error (422)**: `{"message": "Time slot already booked"}` if conflicted.
- **DELETE /api/appointments/{id}**
  - **Purpose**: Remove an appointment.
  - **Headers**: `Authorization: Bearer <token>`
  - **Response (200)**:

    ```json
    {
        "message": "Appointment deleted"
    }
    ```
  - **Error (404)**: `{"message": "Appointment not found"}` if non-existent.

## Postman Usage Examples

### 1. User Registration

Register users for each role before testing other endpoints.

- **Method**: POST
- **URL**: `http://localhost:8000/api/register`
- **Headers**: `Content-Type: application/json`
- **Request Body** (per role):
  - **Admin**:

    ```json
    {
        "name": "Admin User",
        "email": "admin@example.com",
        "password": "password123",
        "role": "admin"
    }
    ```
    - **Expected Response (201)**: Contains a token.
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
  - Save tokens from responses for subsequent requests.

### 2. User Login

- **Method**: POST
- **URL**: `http://localhost:8000/api/login`
- **Headers**: `Content-Type: application/json`
- **Request Body** (e.g., Admin):

  ```json
  {
      "email": "admin@example.com",
      "password": "password123"
  }
  ```
- **Expected Response**: Contains a token.

### 3. Create Appointment (Receptionist)

- **Method**: POST
- **URL**: `http://localhost:8000/api/appointments`
- **Headers**:
  - `Authorization: Bearer <recep_token>`
  - `Content-Type: application/json`
- **Request Body**:

  ```json
  {
      "doctor_id": 2,
      "appointment_time": "2025-06-18 12:00:00"
  }
  ```
- **Expected Response (201)**:

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
- **Request Body**:

  ```json
  {
      "appointment_time": "2025-06-18 12:30:00"
  }
  ```
- **Expected Response (200)**:

  ```json
  {
      "id": 1,
      "doctor_id": 2,
      "appointment_time": "2025-06-18 12:30:00"
  }
  ```
- **Error (422)**: `{"message": "Time slot already booked"}` if conflicted.

### 5. Delete Appointment (Receptionist)

- **Method**: DELETE
- **URL**: `http://localhost:8000/api/appointments/1`
- **Headers**: `Authorization: Bearer <recep_token>`
- **Expected Response (200)**:

  ```json
  {
      "message": "Appointment deleted"
  }
  ```
- **Error (404)**: `{"message": "Appointment not found"}` if non-existent.

## Additional Notes

- Set `APP_DEBUG=false` in `.env` for production to suppress stack traces.
- Role-based access is enforced via custom `RoleMiddleware`.
- Test endpoints sequentially, starting with registration and login.