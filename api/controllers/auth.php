<?php
DI::rest()->post('/auth/register/:usertype', ['first_name', 'last_name', 'street', 'city', 'gender','phone','email'], function (RestData $data) {
    $usertype = $data->pathdata['usertype'];
    $body = $data->request->getBody();

    if (!nullcheck($body, ['first_name', 'last_name', 'street', 'city', 'gender','phone','email'])) {
        http(400, 'Mangler data');
    }

    if (!in_array($usertype, DI::env('USER_REGISTER_TYPES'))) {
        http(404);
    }

    if (!is_email($body['email'])) {
        http(400, 'Email ikke gyldig');
    }

    if (R::findOne($usertype, 'email = ?', [$body['email']])) {
        http(400, 'Brugeren eksisterer i forvejen');
    }

    $body['verify_email_token'] = randstr();
    $body['email_verified'] = false;
    $body['created'] = time();

    $user = R::dispense($usertype);
    $user->import($body);
    R::store($user);

    mail_validate_email($body['email'], $body['verify_email_token'], $usertype);
    http(200);
});

DI::rest()->get('/auth/validate_email/:usertype/:token', function (RestData $data) {
    $usertype = $data->pathdata['usertype'];

    if (in_array($usertype, DI::env('USER_LOGIN_TYPES')) === false) {
        http(404);
    }

    $user = R::findOne($usertype, 'verify_email_token = ?', [$data->pathdata['token']]);
    if ($user) {
        if (isset($user['temp_email'])) {
            $user['email'] = $user['temp_email'];
            $user['temp_email'] = null;
        }
        $user['email_verified'] = true;

        R::store($user);
        http();
    }
    http(400);
});

DI::rest()->get('/auth/validate_login/:usertype/:token', function (RestData $data) {
    $usertype = $data->pathdata['usertype'];

    if (in_array($usertype, DI::env('USER_LOGIN_TYPES')) === false) {
        http(404);
    }

    $user = R::findOne($usertype, 'login_token = ?', [$data->pathdata['token']]);
    if ($user) {
        $user['access_token'] = $usertype[0] . randstr(29);
        $user['login_token'] = null;
        R::store($user);
        http(200, $user['access_token']);
    }
    http(400);
});

DI::rest()->post('/auth/login/:usertype', ['email'], function (RestData $data) {
    $usertype = $data->pathdata['usertype'];
    if (in_array($usertype, DI::env('USER_LOGIN_TYPES')) === false) {
        http(404);
    }

    $body = $data->request->getBody();

    if (!nullcheck($body, ['email'])) {
        http(400, 'Mangler data');
    }
    if (!is_email($body['email'])) {
        http(400, 'Email ikke gyldig');
    }

    $user = R::findOne($usertype, 'email = ?', [$body['email']]);
    if (!$user) {
        http(400, 'Email eksistere ikke');
    }
    if ($user['deleted'] == 1) {
        http(400, 'Bruger er slettet');
    }
    if (!$user['email_verified']) {
        http(400, 'Email ikke valideret');
    }
    $user['login_token'] = randstr();
    R::store($user);
    mail_validate_login($body['email'], $user['login_token'], $usertype);

    http(200);
});

DI::rest()->get('/auth/logout', function (RestData $data) {
    $user = $data->middleware['user'];
    $user['access_token'] = null;
    R::store($user);
    http(200, 'logget ud');
}, ['auth.loggedIn(' . implode(',', $ENV['USER_LOGIN_TYPES']) . ')']);
