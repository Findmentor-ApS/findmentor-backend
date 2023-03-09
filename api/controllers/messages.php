<?php
DI::rest()->post('/message/send', function (RestData $data) {    
    // var_dump($data->request->getBody());
    DI::messager()->send('events-channel', 'my-event', "fdafasds");
    die();
    // $body = $data->request->getBody();
    // $user = $data->middleware['user'];

    // if ($user['phone_verified']) {
    //     http(400, 'Phone already verified');
    // }

    // if ($user['phone_verify'] != $body['code']) {
    //     http(400, 'Bad code');
    // }

    // $user['phone_verified'] = 1;
    // R::store($user);
    // http();

    // mail_validate_email($body['email'], $body['verify_email_token'], $usertype);
    // http(200);
});

?>