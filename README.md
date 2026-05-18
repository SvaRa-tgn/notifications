
1. Скачиваем проект https://github.com/SvaRa-tgn/notifications
2. Копируем .env.example в ту же директорию и переименовываем в .env
3. Билдим проект docker-compose build
4. Запускаем проект docker-compose up -d
5. устанавливаем зависимости composer install
   php artisan optimize:clear 
   php artisan key:generate
   php artisan migrate

Запуск команд для обработки сообщений в контейнере kafka (обязательно запустить перед отправкой сообщений, 
задержка обработки стоит на 1 секунде)
# Транзакционные
php artisan kafka:consume --topic=notifications-tx

# Информационные
php artisan kafka:consume --topic=notifications-info

# Маркетинговые
php artisan kafka:consume --topic=notifications-marketing

Коллекция для Postman лежит в проекте Notification.json

Тесты!!! Запускаем в контейнере - php artisan test
