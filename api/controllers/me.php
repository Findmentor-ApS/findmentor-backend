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
        $user['is_avaliable'] = $body['is_avaliable'];
        $user['description'] = $body['description'];
    }

    R::store($user);

    http(200, $user, true);
}, ['auth.loggedIn']);

// Create endpoint for updating profile picture me/image
DI::rest()->put('/me/image', function (RestData $data) {
    $user = $data->middleware['user'];
    $body = $data->request->getBody();

    $user['profile_picture'] = $body['profile_picture'];
    R::store($user);

    http(200, $user, true);
}, ['auth.loggedIn']);

DI::rest()->get('/me', function (RestData $data) {
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];
    if($usertype == 'mentor') {
        $user['experiences'] = R::find('experience', 'mentor_id = ?', [$user['id']]);
    }
    http(200, $user, true);
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


DI::rest()->put('/me/experiences', function (RestData $data) {
    $usertype = $data->middleware['usertype'];
    $body = $data->request->getBody();
    $user = $data->middleware['user'];

    $experiences = R::find('experience', 'mentor_id = ?', [$user['id']]);
    foreach ($experiences as $experience) {
        R::trash($experience);
    }

    foreach ($body['typeExperiences'] as $value) {
        $experience = R::dispense('experience');
        $experience['mentor_id'] = $user['id'];
        $experience['experience_type'] = $value;
        R::store($experience);
    }

    $updatedExperiences = R::find('experience', 'mentor_id = ?', [$user['id']]);
    http(200, $updatedExperiences, true);
}, ['auth.loggedIn']);

 DI::rest()->put('/me/supportForm', function (RestData $data) {
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    $supportForm = R::find('supportform', 'user_id = ?', [$user['id']]);
    foreach ($supportForm as $support) {
        R::trash($support);
    }

    foreach ($body['supportForm'] as $value) {
        $support = R::dispense('supportform');
        $support['user_id'] = $user['id'];
        $support['support_type'] = $value;
        R::store($support);
    }

    $updatedSupportForm = R::find('supportform', 'user_id = ?', [$user['id']]);
    http(200, $updatedSupportForm, true);
}, ['auth.loggedIn']);


DI::rest()->put('/me/location', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    $locationArr = R::find('location', 'user_id = ?', [$user['id']]);
    foreach ($locationArr as $location) {
        R::trash($location);
    }

    foreach ($body['location'] as $value){
        $location = R::dispense('location');
        $location['user_id'] = $user['id'];
        $location['location'] = $value;
        R::store($location);
    }

    $updatedLocationArr = R::find('location', 'user_id = ?', [$user['id']]);
    http(200, $updatedLocationArr, true);
}, ['auth.loggedIn']);


DI::rest()->put('/me/languages', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    $languagesArr = R::find('language', 'user_id = ?', [$user['id']]);
    foreach ($languagesArr as $language) {
        R::trash($language);
    }

    foreach ($body['languages'] as $value){
        $language = R::dispense('language');
        $language['user_id'] = $user['id'];
        $language['language'] = $value;
        R::store($language);
    }

    $updatedLanguagesArr = R::find('language', 'user_id = ?', [$user['id']]);
    http(200, $updatedLanguagesArr, true);
}, ['auth.loggedIn']);


DI::rest()->put('/me/contacts', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    $contactsArr = R::find('contact', 'user_id = ?', [$user['id']]);
    foreach ($contactsArr as $contact) {
        R::trash($contact);
    }

    foreach ($body['contacts'] as $value){
        $contact = R::dispense('contact');
        $contact['user_id'] = $user['id'];
        $contact['contact'] = $value;
        R::store($contact);
    }

    $updatedContactsArr = R::find('contact', 'user_id = ?', [$user['id']]);
    http(200, $updatedContactsArr, true);
}, ['auth.loggedIn']);

// get bookings for mentor and commune
DI::rest()->put('/me/bookings', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    if($usertype == 'commune') {
        $bookings = R::find('booking', 'commune_id = ?', [$user['id']]);
    }
    elseif($usertype == 'mentor'){
        $bookings = R::find('booking', 'recipient_id = ?', [$user['id']]);
    }
    else{
        $bookings = R::find('booking', 'user_id = ?', [$user['id']]);
    }
}, ['auth.loggedIn']);









