<?php
use Twilio\Rest\Client;

class DI
{
    private static Log $logger;
    private static Rest $rest;
    private static Mail $mail;
    private static Predis\Client $redis;
    private static Cache $cache;
    private static Queue $queue;
    private static GuzzleHttp\Client $http;
    private static Discord $discord;

    public static function logger() {
        if (!isset(SELF::$logger)) {
            SELF::$logger = new Log();
        }

        return SELF::$logger;
    }

    public static function discord() {
        if (!isset(SELF::$discord)) {
            SELF::$discord = new Discord();
        }

        return SELF::$discord;
    }

    public static function rest(): Rest {
        if (!isset(SELF::$rest)) {
            SELF::$rest = new Rest();
        }

        return SELF::$rest;
    }

    public static function mail($to, $subject, $body) {
        if (!isset(SELF::$mail)) {
            SELF::$mail = new Mail();
        }

        return SELF::$mail->send($to, $subject, $body);
    }

    public static function sms($to, $body) {
        $twilio = new Client(SELF::env('TWILIO_SID'), SELF::env('TWILIO_TOKEN'));
        $twilio->messages->create($to, [
            "body" => $body,
            "from" => SELF::env('APP')
        ]);
    }

    /**
     * Recursive method to read from a nested array using dot notation
     * 
     * @example With the following array, we could use env('test.1') to get the index ['test']['1']: ['test' => ['1' => 'test1', '2' => 'test1']]
     */
    public static function env(string $get = null) {
        global $ENV;

        if ($get == null) return $ENV;

        if (strpos($get, '.') !== false) {
            $get = explode('.', $get);
            $envRef = &$ENV;
            foreach ($get as $key) {
                if (!isset($envRef[$key])) {
                    return null;
                }
                $envRef = &$envRef[$key];
            }
            return $envRef;
        } else {
            return isset($ENV[$get]) ? $ENV[$get] : null;
        }
    }

    public static function redis() {
        if (!isset(SELF::$redis)) {
            SELF::$redis =  new Predis\Client(SELF::env('REDIS'));
        }

        return SELF::$redis;
    }

    public static function cache() {
        if (!isset(SELF::$cache)) {
            SELF::$cache =  new Cache(SELF::redis());
        }

        return SELF::$cache;
    }

    public static function queue() {
        if (!isset(SELF::$queue)) {
            SELF::$queue =  new Queue(SELF::redis());
        }

        return SELF::$queue;
    }

    public static function http() {
        if (!isset(SELF::$http)) {
            SELF::$http =  new GuzzleHttp\Client();
        }

        return SELF::$http;
    }
}
