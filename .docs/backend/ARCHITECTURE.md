# Architecture

This project uses the Controller-Service-Repository pattern.

- Request classes control validation and are the primary interface for user input and API documentation
- Controllers should be thin and focused on coordinating business logic, delegating validation and persistence to other components
- Services encapsulate business logic and can be reused across multiple controllers or other services
- Repositories encapsulate persistence logic and can be reused across multiple services

## Structure

```txt
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Requests/
│   │
│   ├── Services/
│   ├── Repositories/
│   ├── DTOs/
│   ├── Enums/
│   ├── Events/
│   ├── Listeners/
│   ├── Jobs/
│   └── Models/
│
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│
├── routes/
│   └── api.php
│
└── tests/
```