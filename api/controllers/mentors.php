<?php
DI::rest()->get('/mentors/:id', function(RestData $data) {
  $mentor = R::findOne('mentor', 'id=? AND is_available = ?', [$data->pathdata['id'], true]);
  
  if (!$mentor) {
    http(404, 'The mentor is not available.');
  } else {
    $mentor = fetchUser($mentor, 'mentor');
    http(200, $mentor, true);
  }
});

DI::rest()->get('/mentors', function(RestData $data) {
  $query = [
    'search' => explode(",",$data->request->getQuery()['search']),
    'location' => explode(",",$data->request->getQuery()['location']),
    'experience' => explode(",",$data->request->getQuery()['experience']),
    'language' => explode(",",$data->request->getQuery()['language']),
    'gender' => explode(",",$data->request->getQuery()['gender']),
    'contact' => explode(",",$data->request->getQuery()['contact']),
    'audience' => explode(",",$data->request->getQuery()['audience']),
  ];

  $page = $data->request->getQuery()['page'];
  $perPage = $data->request->getQuery()['perpage'];
  $mentors = searchMentors($query['search'],$query['location'],
  $query['experience'],
  $query['language'],
  $query['gender'],
  $query['contact'],
  $query['audience'],
  $page, $perPage);
  http(200, $mentors, true);
});

// make a put request of book
DI::rest()->put('/mentors/book', function (RestData $data) {
  $user = $data->middleware['user'];
  $body = $data->request->getBody();

  $booking = R::findOne('booking', 'id=?', [$body['id']]);
  if (!$booking) {
    http(404, 'The booking is not found.');
  } else {
    // Check if the booking is made by the current user
    if ($data->middleware['usertype'] == 'commune' && $booking->commune_id != $user['id']) {
      http(403, 'You are not allowed to update this booking.');
    } else if ($data->middleware['usertype'] == 'user' && $booking->user_id != $user['id']) {
      http(403, 'You are not allowed to update this booking.');
    }

    // check for each in body and update
    foreach ($body as $key => $value) {
      $booking->$key = $value;
    }
    $booking->updated_at = date('Y-m-d H:i:s');
    R::store($booking);
    http(200, $booking, true);
  }

  http(200, true);
}, ['auth.loggedIn']);

DI::rest()->post('/mentors/book', function (RestData $data) {
  $user = $data->middleware['user'];
  $body = $data->request->getBody();
  $booking = R::dispense('booking');
  if($data->middleware['usertype'] == 'commune'){
    $booking->commune_id = $user['id'];
  }else{
    $booking->user_id = $user['id'];
  }
  foreach ($body as $key => $value) {
      $booking->$key = $value;
  }
  $booking->created_at = date('Y-m-d H:i:s');
  $booking->status = 0;
  R::store($booking);

  http(200, true);
}, ['auth.loggedIn']);

// Create endpoint for updating profile picture me/image
DI::rest()->post('/mentors/bookcall', function (RestData $data) {
  $user = $data->middleware['user'];
  $body = $data->request->getBody();
  $booking = R::dispense('call');
  if($data->middleware['usertype'] == 'commune'){
    $booking->commune_id = $user['id'];
  }else{
    $booking->user_id = $user['id'];
  }
  foreach ($body as $key => $value) {
      $booking->$key = $value;
  }
  $booking->created_at = date('Y-m-d H:i:s');
  R::store($booking);

  http(200, true);
}, ['auth.loggedIn']);

// Create endpoint for updating profile picture me/image
DI::rest()->post('/mentors/profilevisited', function (RestData $data) {
  $user = $data->middleware['user'];
  $body = $data->request->getBody();
  $userIdKey = ($data->middleware['usertype'] == 'commune') ? 'commune_id' : 'user_id';
  $today = date('Y-m-d');

  // Check if a visit for the current user already exists today
  $existingVisit = R::findOne('visit', "$userIdKey = ? AND DATE(created_at) = ?", [$user['id'], $today]);
  if ($existingVisit) {
    http(200, true);
    return;
  }

  $visit = R::dispense('visit');
  $visit->$userIdKey = $user['id'];
  foreach ($body as $key => $value) {
      $visit->$key = $value;
  }
  $visit->created_at = date('Y-m-d H:i:s');
  R::store($visit);

  http(200, true);
}, ['auth.loggedIn']);

// Create endpoint for calling mentor
DI::rest()->post('/mentors/profilecalled', function (RestData $data) {
  $user = $data->middleware['user'];
  $body = $data->request->getBody();
  $booking = R::dispense('outgoing');
  if($data->middleware['usertype'] !== 'commune'){
    http(403, 'Kun kommuner kan ringe til mentorer');
  }
  foreach ($body as $key => $value) {
      $booking->$key = $value;
  }
  $booking->created_at = date('Y-m-d H:i:s');
  $booking->commune_id = $user['id'];
  R::store($booking);

  http(200, true);
}, ['auth.loggedIn']);