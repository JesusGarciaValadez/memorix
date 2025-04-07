# Flashcard Module API Documentation

## Overview
This document describes the API endpoints available in the Flashcard module. All endpoints require authentication using Laravel Sanctum.

## Base URL
All API endpoints are prefixed with `/api/v1/`

## Authentication
All endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:
```
Authorization: Bearer <your-token>
```

## Endpoints

### Flashcards

#### List Flashcards
```http
GET /api/v1/flashcards
```

Returns a paginated list of flashcards.

**Response**
```json
{
    "data": [
        {
            "id": 1,
            "question": "What is Laravel?",
            "answer": "A PHP web application framework",
            "created_at": "2024-04-07T12:00:00.000000Z",
            "updated_at": "2024-04-07T12:00:00.000000Z"
        }
    ],
    "links": {
        "first": "http://example.com/api/v1/flashcards?page=1",
        "last": "http://example.com/api/v1/flashcards?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://example.com/api/v1/flashcards",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

#### Create Flashcard
```http
POST /api/v1/flashcards
```

**Request Body**
```json
{
    "question": "What is Laravel?",
    "answer": "A PHP web application framework"
}
```

**Response**
```json
{
    "data": {
        "id": 1,
        "question": "What is Laravel?",
        "answer": "A PHP web application framework",
        "created_at": "2024-04-07T12:00:00.000000Z",
        "updated_at": "2024-04-07T12:00:00.000000Z"
    }
}
```

#### Get Flashcard
```http
GET /api/v1/flashcards/{id}
```

**Response**
```json
{
    "data": {
        "id": 1,
        "question": "What is Laravel?",
        "answer": "A PHP web application framework",
        "created_at": "2024-04-07T12:00:00.000000Z",
        "updated_at": "2024-04-07T12:00:00.000000Z"
    }
}
```

#### Update Flashcard
```http
PUT /api/v1/flashcards/{id}
```

**Request Body**
```json
{
    "question": "What is Laravel?",
    "answer": "A modern PHP web application framework"
}
```

**Response**
```json
{
    "data": {
        "id": 1,
        "question": "What is Laravel?",
        "answer": "A modern PHP web application framework",
        "created_at": "2024-04-07T12:00:00.000000Z",
        "updated_at": "2024-04-07T12:00:00.000000Z"
    }
}
```

#### Delete Flashcard
```http
DELETE /api/v1/flashcards/{id}
```

**Response**
```json
{
    "message": "Flashcard deleted successfully"
}
```

### Study Sessions

#### List Study Sessions
```http
GET /api/v1/study-sessions
```

Returns a paginated list of study sessions.

**Response**
```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "started_at": "2024-04-07T12:00:00.000000Z",
            "ended_at": "2024-04-07T12:30:00.000000Z",
            "created_at": "2024-04-07T12:00:00.000000Z",
            "updated_at": "2024-04-07T12:00:00.000000Z"
        }
    ],
    "links": {
        "first": "http://example.com/api/v1/study-sessions?page=1",
        "last": "http://example.com/api/v1/study-sessions?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://example.com/api/v1/study-sessions",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

#### Create Study Session
```http
POST /api/v1/study-sessions
```

**Request Body**
```json
{
    "started_at": "2024-04-07T12:00:00.000000Z"
}
```

**Response**
```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "started_at": "2024-04-07T12:00:00.000000Z",
        "ended_at": null,
        "created_at": "2024-04-07T12:00:00.000000Z",
        "updated_at": "2024-04-07T12:00:00.000000Z"
    }
}
```

#### End Study Session
```http
PUT /api/v1/study-sessions/{id}
```

**Request Body**
```json
{
    "ended_at": "2024-04-07T12:30:00.000000Z"
}
```

**Response**
```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "started_at": "2024-04-07T12:00:00.000000Z",
        "ended_at": "2024-04-07T12:30:00.000000Z",
        "created_at": "2024-04-07T12:00:00.000000Z",
        "updated_at": "2024-04-07T12:00:00.000000Z"
    }
}
```

### Statistics

#### Get User Statistics
```http
GET /api/v1/statistics
```

Returns statistics for the authenticated user.

**Response**
```json
{
    "data": {
        "total_flashcards": 100,
        "total_study_sessions": 50,
        "total_study_time": 1500,
        "average_session_duration": 30,
        "created_at": "2024-04-07T12:00:00.000000Z",
        "updated_at": "2024-04-07T12:00:00.000000Z"
    }
}
```

### Study Logs

#### List Study Logs
```http
GET /api/v1/logs
```

Returns a paginated list of study logs for the authenticated user.

**Response**
```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "flashcard_id": 1,
            "response": "correct",
            "created_at": "2024-04-07T12:00:00.000000Z",
            "updated_at": "2024-04-07T12:00:00.000000Z"
        }
    ],
    "links": {
        "first": "http://example.com/api/v1/logs?page=1",
        "last": "http://example.com/api/v1/logs?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://example.com/api/v1/logs",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

#### Create Study Log
```http
POST /api/v1/logs
```

**Request Body**
```json
{
    "flashcard_id": 1,
    "response": "correct"
}
```

**Response**
```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "flashcard_id": 1,
        "response": "correct",
        "created_at": "2024-04-07T12:00:00.000000Z",
        "updated_at": "2024-04-07T12:00:00.000000Z"
    }
}
```

## Error Responses

All endpoints may return the following error responses:

### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
    "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
    "message": "Resource not found."
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": [
            "The field name is required."
        ]
    }
}
```

### 500 Server Error
```json
{
    "message": "Server Error"
}
``` 