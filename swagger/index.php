<?php
class Rest
{
    public $data = [];

    public function setBaseEntry($entry)
    {
    }

    private function process($http_method, $entry, $allowed_data = [], $middleware = null)
    {

        $this->data[] = [
            'http_method' => $http_method,
            'entry' => $entry,
            'allowed_data' => $allowed_data,
            'auth' => $middleware ? true : false,
        ];
    }

    public function get($entry, $callable, $middleware = null)
    {
        $this->process('get', $entry, [], $middleware);
    }

    public function post($entry, $allowed_data, $callable, $middleware = null)
    {
        $this->process('post', $entry, $allowed_data, $middleware);
    }

    public function put($entry, $allowed_data, $callable, $middleware = null)
    {
        $this->process('put', $entry, $allowed_data, $middleware);
    }

    public function delete($entry, $allowed_data, $callable, $middleware = null)
    {
        $this->process('delete', $entry, $allowed_data, $middleware);
    }

    public function patch($entry, $allowed_data, $callable, $middleware = null)
    {
        $this->process('patch', $entry, $allowed_data, $middleware);
    }
}

class DI
{
    private static Rest $rest;

    public static function rest(): Rest
    {
        if (!isset(SELF::$rest)) {
            SELF::$rest = new Rest();
        }

        return SELF::$rest;
    }
}

include "../.env.php";
include "../controllers/index.php";

$groups = [];
foreach (DI::rest()->data as &$v) {
    $group = explode('/', $v['entry'])[1];
    if (!array_key_exists($group, $groups)) {
        $groups[$group] = 1;
    } else {
        $groups[$group]++;
    }
}
foreach($groups as $i => $v) {
    if ($v == 1) {
        unset($groups[$i]);
    }
}

$json = [
    "swagger" => "2.0",
    "info" => [
        "title" => $ENV['APP'] . ' ' . $ENV['ENV'],
    ],
    "host" => $ENV['API'],
    "schemes" => ["http"],
    "securityDefinitions" => [
        "Auth" => [
            "type" => "apiKey",
            "name" => "Authorization",
            "in" => "header",
        ],
    ],
];

$json['paths'] = [];
foreach (DI::rest()->data as $v) {
    $entries = explode('/', $v['entry']);
    $path = [];

    foreach ($entries as &$entry) {
        if (strpos($entry, ':') !== FALSE) {
            $path[] = substr($entry, 1);
            $entry = '{' . substr($entry, 1) . '}';
        }
    }
    $v['entry'] = implode('/', $entries);

    $json['paths'][$v['entry']] = [
        $v['http_method'] => [
            "parameters" => [],
            "responses" => [
                "200" => [
                    "description" => "OK",
                ],
            ],
        ],
    ];

    if ($v['auth']) {
        $json['paths'][$v['entry']][$v['http_method']]['security'] = [
            [
                "Auth" => []
            ]
        ];
    }

    if (array_key_exists($entries[1], $groups)) {
        $json['paths'][$v['entry']][$v['http_method']]['tags'] = [$entries[1]];
    }

    foreach ($path as $vv) {
        $json['paths'][$v['entry']][$v['http_method']]['parameters'][] = [
            "name" => $vv,
            "in" => "path",
            "required" => true,
            "type" => "string",
        ];
    }

    if (!empty($v['allowed_data'])) {
        $bodyData = [
            "name" => "body",
            "in" => "body",
            "required" => true,
            "schema" => [
                "type" => "object",
                "properties" => [],
            ],
        ];
        foreach ($v['allowed_data'] as $vv) {
            $bodyData['schema']['properties'][$vv] = [
                "type" => "string",
            ];
        }
        $json['paths'][$v['entry']][$v['http_method']]['parameters'][] = $bodyData;
    }
}

file_put_contents("swagger.json", json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="swagger-ui.css" >
    <link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16" />
    <style>
    html
    {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
    }

    *,
    *:before,
    *:after
    {
        box-sizing: inherit;
    }

    body
    {
        margin:0;
        background: #fafafa;
    }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="swagger-ui-bundle.js"> </script>
    <script src="swagger-ui-standalone-preset.js"> </script>
    <script>
    window.onload = function() {
        // Begin Swagger UI call region
        const ui = SwaggerUIBundle({
            url: "swagger.json",
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout"
        })
        // End Swagger UI call region

        window.ui = ui
    }
    </script>
</body>
</html>
