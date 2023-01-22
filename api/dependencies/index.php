<?php
include 'Mail.php';
include 'Rest.php';
include 'Logger.php';
include 'Cache.php';
include 'Discord.php';
include 'DI.php';
include "Redbean.php";

R::setup('mysql:host=' . DI::env('DBHOST') . ';dbname=' . DI::env('DBNAME'), DI::env('DBUSER'), DI::env('DBPASS'));
R::useFeatureSet('novice/latest');
R::ext('xdispense', function ($type) {
    return R::getRedBean()->dispense($type);
});

set_error_handler("error_handler");
register_shutdown_function(function () {
    $error = error_get_last();

    if ($error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];

        error_handler($errno, $errstr, $errfile, $errline);
    }
});

function error_handler($errno, $errstr, $errfile, $errline)
{
    $errortype = "error";
    switch ($errno) {
        case E_ERROR:
            $errortype = "Error";
            break;
        case E_WARNING:
            $errortype = "Warning";
            break;
        case E_PARSE:
            $errortype = "Parse Error";
            break;
        case E_NOTICE:
            $errortype = "Notice";
            break;
        case E_CORE_ERROR:
            $errortype = "Core Error";
            break;
        case E_CORE_WARNING:
            $errortype = "Core Warning";
            break;
        case E_COMPILE_ERROR:
            $errortype = "Compile Error";
            break;
        case E_COMPILE_WARNING:
            $errortype = "Compile Warning";
            break;
        case E_USER_ERROR:
            $errortype = "User Error";
            break;
        case E_USER_WARNING:
            $errortype = "User Warning";
            break;
        case E_USER_NOTICE:
            $errortype = "User Notice";
            break;
        case E_STRICT:
            $errortype = "Strict Notice";
            break;
        case E_RECOVERABLE_ERROR:
            $errortype = "Recoverable Error";
            break;
        default:
            $errortype = "Unknown error ($errno)";
            break;
    }
    DI::logger()->log($errortype . ': ' . $errstr, [
        "Error Type" => $errortype,
        "Error Message" => $errstr,
        "File" => "$errfile:$errline"
    ], LOGGERS::php, LEVELS::error);
}
