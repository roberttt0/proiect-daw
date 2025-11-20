# Simple PHP MVC (no frameworks) + Docker + MySQL

This is the previous minimal **Model-View-Controller** PHP project (pure PHP, no Composer/framework) with Docker files and a MySQL service added.

## Quick start (with MySQL)
1. Make sure Docker and docker-compose are installed.
2. From the project folder run:
```bash
docker-compose up --build
```
3. Wait for MySQL to initialize, then open `http://localhost:8080`

MySQL defaults included in `docker-compose.yml`:
- database: `appdb`
- user: `appuser`
- password: `secret123`
- root password: `rootpass123`

## Notes
- The PHP app reads DB connection information using `getenv()` and connects with PDO via `app/Core/Database.php`.
- You can change credentials in `docker-compose.yml`.
