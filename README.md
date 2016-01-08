#A faire avant de lancer le projet
1. Lancer la commande "git update-index --assume-unchanged mvc/"
2. Modifier le fichier "mvc/constants.php" et remplacer la ligne "/var/www/soap-project/" par le chemin du projet (sans oublier le "/" de fin). Modifier aussi "DATABASE_USER" et "DATABASE_PASSWORD" pour mettre le nom d'utilisateur et le mot de passe de votre base 
3. Lancer la commande "php composer.phar install"
4. Lancer la commande "mysql -u votre_login_sql -p < createDatabase.sql"
