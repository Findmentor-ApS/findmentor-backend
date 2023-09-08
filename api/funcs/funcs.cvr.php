<?php

function searchCompany($cvr)
{
    $response = DI::http()->request('POST', DI::env('CVR_API'), [
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
