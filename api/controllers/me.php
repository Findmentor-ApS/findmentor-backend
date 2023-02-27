<?php
DI::rest()->delete('/me', [], function (RestData $data) {
    $user = $data->middleware['user'];

    $user['deleted'] = 1;
    $user['access_token'] = null;
    R::store($user);

    http();
}, ['auth.loggedIn']);

DI::rest()->put('/me', function (RestData $data) {
    $usertype = $data->middleware['usertype'];
    $user = $data->middleware['user'];
    $body = $data->request->getBody();


    if (isset($body['email']) && $body['email'] != $user['email']) {
        if (!is_email($body['email'])) {
            http(400, 'Bad email');
        }

        if (R::findOne($usertype, 'email = ?', [$body['email']])) {
            http(400, 'Email exists');
        }

        $verify_email = randstr();
        mail_confirm($body['email'], $verify_email, $usertype);
        $user['verify_email'] = $verify_email;
        $user['email_verified'] = 0;
        $user['verify_email_token_date_created'] = time();
    }

    // if (nullcheck($body, ['phone', 'country_code']) && $body['phone'] != $data['user']['phone']) {
    //     if (!is_phone($body['country_code'] . $body['phone'])) {
    //         http(400, 'Bad phone');
    //     }

    //     $verify_phone = randstr(6, "0123456789");
    //     \DI::sms($body['country_code'] . $body['phone'], "Din verificeringskode er: " . $verify_phone);
    //     $user['phone_verify'] = $verify_phone;
    //     $user['phone_verified'] = 0;
    //     $user['phone_verify_code_date_created'] = time();
    // }

    // store user data based on edit-profile variables
    if($usertype == 'commune') {
        $user['commune_name'] = $body['commune_name'];
        $user['first_name'] = $body['first_name'];
        $user['last_name'] = $body['last_name'];
        $user['phone'] = $body['phone'];
        $user['email'] = $body['email'];
        $user['department'] = $body['department'];
        $user['ean_nr'] = $body['ean_nr'];
    }
    elseif($usertype == 'mentor'){
        $user['first_name'] = $body['first_name'];
        $user['last_name'] = $body['last_name'];
        $user['street'] = $body['street'];
        $user['street_no'] = $body['street_no'];
        $user['street_no'] = $body['street_no'];
        $user['street_side'] = $body['street_side'];
        $user['post_code'] = $body['post_code'];
        $user['city'] = $body['city'];
        $user['education'] = $body['education'];
        $user['gender'] = $body['gender'];
        $user['phone'] = $body['phone'];
        $user['email'] = $body['email'];
        $user['linkedin'] = $body['linkedin'];
        $user['description'] = $body['description'];
    }

    R::store($user);

    http(200, $user, true);
}, ['auth.loggedIn']);

DI::rest()->get('/me', function (RestData $data) {
    http(200, $data->middleware['user'], true);
}, ['auth.loggedIn']);

DI::rest()->get('/me/verify_phone_time', function (RestData $data) {
    $user = $data->middleware['user'];
    $output = [];
    if (!$user['email_verified']) {
        http(400, "Email not verified");
    }

    if ($user['phone_verified']) {
        http(200, "Phone already verified");
    }

    if (!$user['phone_verified'] && $user['phone_verify_code_date_created']) {
        $output['phone'] = $user['phone'];

        if ($user['phone_verify_code_date_created'] + DI::env('PHONE_DELAY') > time()) {
            $output['time'] = $user['phone_verify_code_date_created'] + DI::env('PHONE_DELAY') - time();
        }
    }

    http(200, $output, true);
}, ['auth.loggedIn']);

DI::rest()->post('/me/verify_phone', ['code'], function (RestData $data) {
    $body = $data->request->getBody();
    $user = $data->middleware['user'];

    if ($user['phone_verified']) {
        http(400, 'Phone already verified');
    }

    if ($user['phone_verify'] != $body['code']) {
        http(400, 'Bad code');
    }

    $user['phone_verified'] = 1;
    R::store($user);
    http();
}, ['auth.loggedIn']);
