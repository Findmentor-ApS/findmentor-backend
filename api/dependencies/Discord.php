<?php
class Discord {
    private function send($url, $data) {
        $options = [
            "http" => [
                "header" => "Content-type: application/json",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];
        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
    }

    public function error($error, $data) {
        $fields = [];
        foreach ($data as $name => $value) {
            $fields[] = [
                "name" => $name,
                "value" => $value
            ];
        }
        $log = [
            "content" => "",
            "username" => DI::env("APP") . " " . DI::env("ENV"),
            "embeds" => [
                [
                    "color" => 14423100,
                    "title" => "",
                    "description" => $error,
                    "fields" => $fields
                ]
            ]
        ];
        $this->send(DI::env("DISCORD_WEBHOOKS.ERROR"), $log);
    }
}
