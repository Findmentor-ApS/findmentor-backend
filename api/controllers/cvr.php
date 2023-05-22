<?php
DI::rest()->post('/search/company', function (RestData $data) {
    $body = $data->request->getBody();
    $cvr = $body['cvr'];

    $result = searchCompany($cvr);

    http(200, $result, true);
}, ['auth.loggedIn']);
