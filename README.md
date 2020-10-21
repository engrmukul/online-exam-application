# Online Exam Application

## Usage
    git clone https://github.com/engrmukul/online-exam-application
    cd online-exam-application
    docker-compose up -d --build site
    cd src
    composer update
    cp .env.example .env
    php artisan key:generate
    php artisan migrate:fresh --seed
    php artisan serve
    
[![Watch the video](http://rongtulibd.com/oets.png)](https://www.youtube.com/embed/YQ_PWpHfCfo)
