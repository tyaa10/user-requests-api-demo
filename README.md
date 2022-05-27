# User Requests API Demo PHP+Laravel Project
- php 8;
- MySQL 5.7;
- Laravel 9.
## Run
- php artisan migrate
- php artisan queue:listen
- php artisan serve --port=8080
## REST API Documentation
- **generate**: php artisan l5-swagger:generate
- **watch**: http://127.0.0.1:8080/api/documentation
## Run tests
composer test
