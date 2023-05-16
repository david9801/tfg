# Proyecto TFG
Aplicación Symfony para servicio de videoconferencias para una plataforma de e-learning educativa 

## Installing:

1. Clone repository: `git clone https://github.com/david9801/tfg`.
2. Move to project folder: `cd tfg`.
3. Duplicate .env.test file and set your variables.
4. Caution , database.php file has changed (  'engine' => 'InnoDB',). This change works correctly in both MYSQL and MARIADB.
5. Install project dependencies: `composer install`.
6. `composer dump-autoload`.
7. Create a new schema in your database.
8. Execute migration: `php bin/console doctrine:migrations:migrate`.
9. Start your server.
10. You need to create fixtures in your DB.
11. Read the license

## Tests:
Run: `vendor/bin/phpunit tests `.

