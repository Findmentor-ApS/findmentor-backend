<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log
{
    /**
     * @var Logger[]
     */
    private $loggers = [];

    public function __construct()
    {
        foreach (LOGGERS::array() as $logger) {
            $this->loggers[$logger] = new Logger($logger);
            $this->loggers[$logger]->pushHandler(new StreamHandler(__DIR__ . "/../logs/$logger.log"));
        }
    }

    public function log($message, $data = [], $log = LOGGERS::misc, $level = LEVELS::info)
    {
        if (!in_array($log, LOGGERS::array())) {
            $this->loggers['misc']->error("Could not find logger: $log");
        }

        if (!in_array($level, LEVELS::array())) {
            $this->loggers['misc']->error("Could not find logger level: $level");
        }

        $this->loggers[$log]->$level($message, $data);

        if ($level == LEVELS::error) {
            error_log($message);
            DI::discord()->error($message, $data);
        } else {
            file_put_contents('php://stdout', $message . ': ' . json_encode($data) . PHP_EOL);
        }
    }

    public function info($message, $data = [], $log = LOGGERS::misc)
    {
        $this->log($message, $data, $log, LEVELS::info);
    }
}

abstract class LOGGERS
{
    const email = 'email';
    const sms = 'sms';
    const database = 'database';
    const php = 'php';
    const misc = 'misc';

    static function array() {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}

abstract class LEVELS
{
    const info = 'info';
    const error = 'error';

    static function array() {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
