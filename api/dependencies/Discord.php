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
        $this->send(DI::env("DISCORD_WEBHOOKS_URL"), [
            "content" => $error . '```' . json_encode($data, JSON_PRETTY_PRINT) . '```',
            "username" => DI::env("APP_URL"),
        ]);
    }
}
