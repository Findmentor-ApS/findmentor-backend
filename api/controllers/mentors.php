<?php
DI::rest()->get('/mentors/:id', function(RestData $data) {
  http(200, R::findOne($data->pathdata['id']), true);
});

DI::rest()->get('/mentors', function(RestData $data) {
  http(200, getMentorsSearch($data->request->getQuery()), true);
}, ['auth.loggedIn']);
