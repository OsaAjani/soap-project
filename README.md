#A faire avant de lancer le projet
1. Lancer la commande "git ls-files -z mvc/ | xargs -0 git update-index --assume-unchanged"
2. Modifier le fichiers "mvc/constants.php" pour adapter les constantes PWD, HTTP_PWD (pour les deux, n'oubliez pas le "/" de fin) et DATABASE_USER et DATABASE_PASSWORD.
3. Lancer la commande "php composer.phar install"
4. Lancer la commande "mysql -u votre_login_sql -p < createDatabase.sql"
