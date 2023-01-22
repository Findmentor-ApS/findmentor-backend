<?php
echo shell_exec("php composer update");
echo shell_exec("php composer install");

echo shell_exec("php database/seeder.php");

echo shell_exec("php -S 0.0.0.0:8080");
