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

    $validFields = [
        'commune' => ['commune_name', 'first_name', 'last_name', 'phone', 'email', 'department', 'ean_nr'],
        'mentor' => ['first_name', 'last_name', 'street', 'street_no', 
            'street_side', 'post_code', 'city', 'education', 'gender', 
            'phone', 'email', 'linkedin', 'description', 'cvr', 'company_name'],
        'user' => ['first_name', 'last_name', 'street', 'street_no', 'street_side', 'post_code', 'city', 'gender', 'phone', 'email']
    ];

    // Check if the usertype exists in our validFields array
    if (isset($validFields[$usertype])) {
        foreach ($body as $field => $value) {
            // If this field is valid for the current user type, store it
            if (in_array($field, $validFields[$usertype])) {
                $user[$field] = $value;
            }
        }
    } else {
        http(400, 'Invalid user type');
    }

    R::store($user);
    $user = fetchProfile($user, $usertype);

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
    $user = fetchProfile($user, $usertype);

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


DI::rest()->post('/me/price', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];

    $user['price'] = $body['price'];
    R::store($user);

    http(200, $user, true);
}, ['auth.loggedIn']);

DI::rest()->post('/me/approach', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];

    $user['approach'] = $body['approach'];
    R::store($user);

    http(200, $user, true);
}, ['auth.loggedIn']);


DI::rest()->put('/me/languages', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    $languagesArr = R::find('language', 'mentor_id = ?', [$user['id']]);
    foreach ($languagesArr as $language) {
        R::trash($language);
    }

    foreach ($body['typeLanguages'] as $value){
        $language = R::dispense('language');
        $language['mentor_id'] = $user['id'];
        $language['language_type'] = $value;
        R::store($language);
    }

    $updatedLanguagesArr = R::find('language', 'mentor_id = ?', [$user['id']]);
    http(200, $updatedLanguagesArr, true);
}, ['auth.loggedIn']);

DI::rest()->put('/me/locations', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    $locationsArr = R::find('location', 'mentor_id = ?', [$user['id']]);
    foreach ($locationsArr as $location) {
        R::trash($location);
    }

    foreach ($body['typeLocations'] as $value){
        $location = R::dispense('location');
        $location['mentor_id'] = $user['id'];
        $location['location_type'] = $value;
        R::store($location);
    }

    $updatedLocationsArr = R::find('location', 'mentor_id = ?', [$user['id']]);
    http(200, $updatedLocationsArr, true);
}, ['auth.loggedIn']);

DI::rest()->put('/me/contacts', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];

    $contactsArr = R::find('contact', 'mentor_id = ?', [$user['id']]);
    foreach ($contactsArr as $contact) {
        R::trash($contact);
    }

    foreach ($body['typeContacts'] as $value){
        $contact = R::dispense('contact');
        $contact['mentor_id'] = $user['id'];
        $contact['contact_type'] = $value;
        R::store($contact);
    }

    $updatedContactsArr = R::find('contact', 'mentor_id = ?', [$user['id']]);
    http(200, $updatedContactsArr, true);
}, ['auth.loggedIn']);

DI::rest()->put('/me/audiences', function (RestData $data){
    $body = $data->request->getBody();
    $user = $data->middleware['user'];

    $audiencesArr = R::find('audience', 'mentor_id = ?', [$user['id']]);
    foreach ($audiencesArr as $audience) {
        R::trash($audience);
    }

    foreach ($body['typeAudiences'] as $value){
        $audience = R::dispense('audience');
        $audience['mentor_id'] = $user['id'];
        $audience['audience_type'] = $value;
        R::store($audience);
    }

    $updatedAudienceArr = R::find('audience', 'mentor_id = ?', [$user['id']]);
    http(200, $updatedAudienceArr, true);
}, ['auth.loggedIn']);


// original
DI::rest()->get('/me/bookings', function (RestData $data) {
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];
    $page = $data->request->getQuery()['page'];
    $perPage = $data->request->getQuery()['perpage'];
    $status = $data->request->getQuery()['status'];
    $offset = ($page - 1) * $perPage;
    $bookings = [];
    
    if($usertype == 'mentor') {
        // check if status is 0
        $bookings = R::find('booking', 'mentor_id = ? AND status = ? LIMIT ? OFFSET ?', [$user['id'],$status, $perPage, $offset]);        // $bookings = R::find('booking', 'mentor_id = ? LIMIT ? OFFSET ?', [$user['id'], $perPage, $offset]);
        foreach ($bookings as $booking) {
            if($booking['user_id'] != null) $booking['users'] = getUserInfo($booking['user_id']);
            if($booking['commune_id'] != null) $booking['communes'] = getCommuneInfo($booking['commune_id']);
        }
        $bookings['total'] = R::count('booking', 'mentor_id = ?  AND status = ?', [$user['id'],$status]);
    } else if($usertype == 'commune') {
        $bookings = R::find('booking', 'commune_id = ? LIMIT ? OFFSET ?', [$user['id'], $perPage, $offset]);
        foreach ($bookings as $booking) {
            $booking['mentor'] = getMentorInfo($booking['mentor_id']);
        }
        $bookings['total'] = R::count('booking', 'commune_id = ?', [$user['id']]);
    } else if($usertype == 'user') {
        $bookings = R::find('booking', 'user_id = ? LIMIT ? OFFSET ?', [$user['id'], $perPage, $offset]);
        foreach ($bookings as $booking) {
            $booking['mentor'] = getMentorInfo($booking['mentor_id']);
        }
        $bookings['total'] = R::count('booking', 'user_id = ?', [$user['id']]);
    }

    http(200, $bookings, true);
}, ['auth.loggedIn']);

DI::rest()->put('/me/bookings/:id/accept', function (RestData $data) {
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    // if the userType is not a mentor, then return 404
    if($usertype != 'mentor') {
        http(404, "Du er ikke en mentor.");
    }

    $booking = R::findOne('booking', 'id = ? AND mentor_id = ?', [$body['id'], $user['id']]);
    if (!$booking) {
        http(404, "Booking ikke fundet.");
    }

    $booking['status'] = 1; 
    R::store($booking);

    http(200, $booking, true);
}, ['auth.loggedIn']);

DI::rest()->put('/me/bookings/:id/decline', function (RestData $data) {
    $body = $data->request->getBody();
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];

    // if the userType is not a mentor, then return 404
    if($usertype != 'mentor') {
        http(404, "Du er ikke en mentor.");
    }

    $booking = R::findOne('booking', 'id = ? AND mentor_id = ?', [$body['id'], $user['id']]);
    if (!$booking) {
        http(404, "Booking ikke fundet.");
    }

    $booking['status'] = -1; 
    R::store($booking);

    http(200, $booking, true);
}, ['auth.loggedIn']);




DI::rest()->get('/me/calls', function (RestData $data) {
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];
    $page = $data->request->getQuery()['page'];
    $perPage = $data->request->getQuery()['perpage'];
    $offset = ($page - 1) * $perPage;
    $calls = [];

    
    
    if($usertype == 'mentor') {
        // check if status is 0
        $calls = R::find('call', 'mentor_id = ? AND (is_deleted IS NULL OR is_deleted != 1) LIMIT ? OFFSET ?', [$user['id'], $perPage, $offset]);        // $bookings = R::find('booking', 'mentor_id = ? LIMIT ? OFFSET ?', [$user['id'], $perPage, $offset]);
        foreach ($calls as $call) {
            if($call['user_id'] != null) $call['users'] = getUserInfo($call['user_id']);
            if($call['commune_id'] != null) $call['communes'] = getCommuneInfo($call['commune_id']);
        }
        $calls['total'] = R::count('call', 'mentor_id = ? AND (is_deleted IS NULL OR is_deleted != 1)', [$user['id']]);
    } else if($usertype == 'commune') {
        $calls = R::find('call', 'commune_id = ? AND (is_deleted IS NULL OR is_deleted != 1) LIMIT ? OFFSET ?', [$user['id'], $perPage, $offset]);
        foreach ($calls as $call) {
            $call['mentor'] = getMentorInfo($call['mentor_id']);
        }
        $calls['total'] = R::count('call', 'commune_id = ? AND (is_deleted IS NULL OR is_deleted != 1)', [$user['id']]);
    } else if($usertype == 'user') {
        $calls = R::find('call', 'user_id = ? AND (is_deleted IS NULL OR is_deleted != 1) LIMIT ? OFFSET ?', [$user['id'], $perPage, $offset]);
        foreach ($calls as $call) {
            $call['mentor'] = getMentorInfo($call['mentor_id']);
        }
        $calls['total'] = R::count('call', 'user_id = ? AND (is_deleted IS NULL OR is_deleted != 1)', [$user['id']]);
    }

    http(200, $calls, true);
}, ['auth.loggedIn']);

// Change settings for user,mentor or commune
DI::rest()->put('/me/settings', function (RestData $data) {
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];
    $body = $data->request->getBody();

    if($usertype == 'mentor'){
        $user['is_available'] = $body['is_available'];
    }
    R::store($user);

    http(200, $user, true);
}, ['auth.loggedIn']);

// delete account for user,mentor or commune have 30 days to restore
DI::rest()->delete('/me/delete', function (RestData $data) {
    $user = $data->middleware['user'];

    $user['is_deleted'] = true;
    $user['deleted_at'] = date('Y-m-d H:i:s');

    R::store($user);

    http(200, $user, true);
}, ['auth.loggedIn']);

DI::rest()->put('/me/calls/:id/delete', function (RestData $data) {
    $user = $data->middleware['user'];
    $usertype = $data->middleware['usertype'];
    $body = $data->request->getBody();

    if($usertype != 'mentor'){
        http(403, "You are not a mentor.");
    }

    $call = R::findOne('call', 'id = ? AND mentor_id = ?', [$body['id'], $user['id']]);
    if (!$call) {
        http(404, "Call not found.");
    }

    $call['is_deleted'] = true;
    $call['deleted_at'] = date('Y-m-d H:i:s');

    R::store($call);

    http(200, $call, true);
}, ['auth.loggedIn']);











