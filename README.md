 # Projet 5 - Créer votre propre blog en php

 [![Codacy Badge](https://api.codacy.com/project/badge/Grade/4a1cc3b19be74d1780a36cef4fdd041e)](https://app.codacy.com/gh/alli83/projet5?utm_source=github.com&utm_medium=referral&utm_content=alli83/projet5&utm_campaign=Badge_Grade_Settings)

Create a php blog without using Symfony or any other framework.
However, it is possible to use librairies downloaded via Composer. 

Main goals: 
*   Create a home page (cv to download, contact form etc..)
*   Display posts with image (optionnal).
*   Create an admin dashboard only accessible for admin users, one where they can manage posts, members and comments
*   Authorize logged in users to write comments (comments need to be validated by Admin user before being published)

## Requirements

You need Apache, PHP (version 7.4) and MySQL. 
You can use MAMP/ WAMP => installs a local server environment

## Installation

*   You need to install Composer. 

You can run the installer locally, in your current project directory, or globally. More instructions can be found  here:
[install composer](https://getcomposer.org/download/)

In order to create the vendor folder run:
```bash
composer install
```
In order to include your own classes from src directory under the namespace App (for example), in composer.json make sure you have:
```bash
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
```
*   In dev mode, to intercept emails, you can use maildev 
```bash
npm install -g maildev
```
then run 
```bash
maildev 
```
## Configuration 

*   debug: Whoops => in index.php
```bash 
$whoops = new \Whoops\Run();
       $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
       $whoops->register();
```
*   In order to set up debug mode in Twig , make sure you have in src/View/View.php: 
```bash
 $this->twig = new Environment($loader, [
            'debug' => true]);
```
*   For email, database and cookie configuration: you need to create config.php file (src/config) based on config.php.dist with your own datas(password, database, name etc). config.php is loaded by ConfigSetUp which is loaded by Database.php et MailerService.php and Session.php
```bash
return array(
    'database' => array(
        "db_user" => "name",
        "db_pass" => "password",
        "db_host" => "127.0.0.1:0000",
        "db_name" => "databasename"
    ),
    'emailTransport' => array(
        "smtp" => "127.0.0.1:0000",
        "smtp_port" => 1025,
        "from" => "test@test.fr",
        "sender" => "nom de l'expediteur"
    ),
    'cookie' => array(
        "lifetime" => int,
        "path" => '/',
        "secure" => 'bool',
        "httponly" => 'bool',
        "samesite" => 'lax'
    )
);
```
*   Profil picture in public/images folder (profile.jpeg)
*   At the root of the project, for the db's structure, you can find a db.sql file => you can import it. 
*   Don't forget to reset the password: /login => "jai oublié mon mot de passe".
*   From your superAdmin account, you can manage "admin" and "user" permissions directly from the admin dashboard.
*   If you decide to create your own database from zero (without any of the examples given in db.sql), don't forget that you need to create first a superAdmin user => go to signup : create an account and then, directly in your db (through phpmyadmin for example) change the permission (role) to 'superAdmin'. 
*   When you are ready, at the root of your project, to launch it, run :
```bash
php -S localhost:8000 -t public
```
## Code quality

you can run : 
```bash
vendor/bin/phpcbf --standard=PSR12 src
```
```bash
vendor/bin/phpstan analyse src --level 0  (from level 0 to 8)
```