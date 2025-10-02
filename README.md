# (Laravel 11 + PHP 8.2)

Тестовый проект по заданию для Effective Mobile. Реализован REST API для задач и статусов с авторизацией через Laravel Sanctum, присутствует Swagger (OpenAPI) документация и готовое Docker-окружение.

---

## Запуст через Docker

> Требования: Docker + Docker Compose.

1) Склонируйте репозиторий и перейдите в директорию проекта:
```bash
git clone <repo-url>
Перейдите в директорию проекта
Создайте .env с параметрами:
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=eff_mobile
DB_USERNAME=user
DB_PASSWORD=12345678

Далее запустите контейнеры:

bash
docker compose up -d --build

Доступы по умолчанию

API: http://localhost

Swagger UI: http://localhost/docs

Swagger JSON: http://localhost/api/documentation

phpMyAdmin: http://localhost:8080 (логин/пароль из .env, хост — db)

Postman

Готовая коллекция находится в корне репозитория: /postman.
{{base_url}} укажите http://localhost/api/