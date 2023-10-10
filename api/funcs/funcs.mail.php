<?php
function mail_validate_email($m, $token, $type)
{
    $appname = DI::env('APP_NAME');
    $url = "http://" . DI::env('APP_URL') . "/auth/validate_email/$type/$token";
    $subject = "Validate Email " . $appname;
    $message = <<<EOF
    <big><big>
    Hi,<br/><br/>

    Thank you for taking the time to register at $appname. To validate your email, simply click the link below:<br/>
    <a href="$url">$url</a><br/><br/>

    If you didn't sign up for $appname, Please ignore this mail, and we are sorry for any inconvenience we may have caused you.<br/>
    Thanks in advance,<br/>
    $appname
    </big></big>
EOF;

    return DI::mail($m, $subject, $message);
}

function mail_validate_login($m, $token, $type)
{
    $appname = DI::env('APP_NAME');
    $url = "http://" . DI::env('APP_URL') . "/auth/validate_login/$type/$token";
    $subject = "Validate login " . $appname;
    $message = <<<EOF
    Validate Email <a href='$url'>Here</a>
EOF;

    return DI::mail($m, $subject, $message);
}

function mail_confirm($m, $token, $type)
{
    $appname = DI::env('APP_NAME');
    $url = "http://" . DI::env('APP_URL') . "/auth/validate_email/$type/$token";
    $subject = "Validate Email " . $appname;
    $message = <<<EOF
    <big><big>
    Hi,<br/><br/>

    To validate your email, simply click the link below:<br/>
    <a href="$url">$url</a><br/><br/>

    If you didn't update your email for $appname, you should contact support immediately.<br/>
    Thanks in advance,<br/>
    $appname
    </big></big>
EOF;

    return DI::mail($subject, $message, $m);
}
