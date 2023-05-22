<?php
function searchCompany($cvr) {
    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', 'http://distribution.virk.dk/cvr-permanent/virksomhed/_search', [
        'auth' => [DI::env("CVR_USERNAME"), DI::env("CVR_PASSWORD")],
        'json' => [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'Vrvirksomhed.cvrNummer' => $cvr
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]);

    return json_decode($response->getBody()->getContents(), true);
}
