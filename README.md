# projet5
php -S localhost:8000 -t public
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/4a1cc3b19be74d1780a36cef4fdd041e)](https://app.codacy.com/gh/alli83/projet5?utm_source=github.com&utm_medium=referral&utm_content=alli83/projet5&utm_campaign=Badge_Grade_Settings)




* Installer composer
* Mettre en place l'autoloading
* Installer les packages Whoops, PHP_CodeSniffer, PhpStan avec composer et en utilisant le flag --dev
* Installer twig avec composer

* Vérifier que le path de php.exe est bien dans les variables d'environnement https://www.php.net/manual/fr/faq.installation.php#faq.installation.addtopath
* Pour lancer le serveur PHP depuis la racine du projet => php -S localhost:8000 -t public

* Avant chaque commit faire un :
    * vendor/bin/phpcbf --standard=PSR12 src
    * vendor/bin/phpstan analyse src --level 0
        * corriger les erreurs et passer au level suivant --level 1
        * ainsi de suite jusqu'au level 8
