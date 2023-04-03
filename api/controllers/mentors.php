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