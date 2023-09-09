<?php

echo shell_exec("php composer update");
echo shell_exec("php composer install");

$ENV = parse_ini_file('../../.env');
if ($ENV['SEED']) {
    echo shell_exec("php database/seeder.php");
}

echo shell_exec("php -S 0.0.0.0:8080");
