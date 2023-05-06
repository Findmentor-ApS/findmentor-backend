<?php
DI::rest()->get('/mentors/:id', function(RestData $data) {
  $mentor = R::findOne('mentor', 'id=?', [$data->pathdata['id']]);
  $mentor['experiences'] = R::find('experience', 'mentor_id=?', [$data->pathdata['id']]);
  http(200, $mentor, true);
});

DI::rest()->get('/mentors', function(RestData $data) {
  $query = [
    'search' => $data->request->getQuery('search'),
    'location' => $data->request->getQuery('location'),
    'typeForm' => $data->request->getQuery('typeForm'),
    'language' => $data->request->getQuery('language'),
    'gender' => $data->request->getQuery('gender'),
    'contact' => $data->request->getQuery('contact'),
    'target' => $data->request->getQuery('target'),
  ];
  $page = $data->request->getQuery('page');
  $perPage = $data->request->getQuery('perpage');
  $mentors = getMentorsSearch($query, $page, $perPage);
  http(200, $mentors, true);
});

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
  R::store($booking);

  http(200, true);
}, ['auth.loggedIn']);
