# User Microservice

Краткое описание вашего проекта.
Микросервис для юзеров с регистрацией, аутентификацией, разработанный с использованием принципов Чистой Архитектуры и DDD на базе Laravel.

## Требования

Для запуска проекта вам потребуется:

* **Git**: Для клонирования репозитория.
* **Docker Desktop** (или Docker Engine и Docker Compose): Для запуска приложения в контейнерах.
    * Убедитесь, что Docker запущен.

---

## Установка и запуск

Следуйте этим шагам для развертывания проекта:

### Клонирование репозитория

Откройте терминал и выполните команду:

```sh
git clone https://github.com/andrushk4/user-service.git
```

### Настройка окружения

#### 1. Копируем файл .env.example в .env
```sh
cp .env.example .env
```
#### 2. Установим composer зависимости в локальном окружении
```sh
docker compose run --rm app composer install
```
#### 3. Запускаем Docker Compose
```sh
docker compose up -d --build
```
#### 3. Устанавливаем приложение
```sh
docker compose exec app php artisan app:install
```

Базовый URL: http://localhost:8000