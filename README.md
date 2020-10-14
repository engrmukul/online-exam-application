# Online Exam Application

## Usage
    docker-compose up -d --build site
    cd src
    composer update
    php artisan migrate:fresh --seed
    php artisan serve