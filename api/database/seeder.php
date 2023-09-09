<?php
include __DIR__ . "/../vendor/autoload.php";
include __DIR__ . "/../funcs/funcs.util.php";
include __DIR__ . "/../env.php";
include __DIR__ . "/../dependencies/index.php";

$faker = Faker\Factory::create();

if (!R::findOne('user')) {
    $users = [];
    for ($i = 1; $i <= 100; $i++) {
        $users[] = R::dispense('user')->import([
            "email" => "$i@findmentor.dk"
        ]);
    }
    R::storeAll($users);
    DI::logger()->info("Seeded Table user",  [], LOGGERS::database);
}

if (!R::findOne('mentor')) {
    $mentors = [];
    for ($i = 1; $i <= 100; $i++) {
        $mentor = R::dispense('mentor');
        $mentor->email = "$i@findmentor.dk";
        foreach ($faker->randomElements(DI::env('DATA.MENTORTYPES'), random_int(1, 3)) as $mentortype) {
            $mt = R::dispense('mentortype');
            $mt->type = $mentortype;

            $mentor->ownMentortypeList[] = $mt;
        }
        $mentors[] = $mentor;
    }
    R::storeAll($mentors);
    DI::logger()->info("Seeded Table mentor",  [], LOGGERS::database);
}

if (!R::findOne('commune')) {
    $i = 1;
    $commune_users = [];
    foreach (DI::env('DATA.COMMUNES') as $short => $name) {
        $commune_users[] = R::dispense('commune')->import([
            "firstname" => $name,
            "email" => "$i@findmentor.dk"
        ]);
        $i++;
    }
    R::storeAll($commune_users);
    DI::logger()->info("Seeded Table commune",  [], LOGGERS::database);
}
