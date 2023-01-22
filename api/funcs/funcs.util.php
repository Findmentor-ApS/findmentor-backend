<?php
function perm($user, $perm)
{
    if ($user['usertype'] == 'admin') {
        return true;
    }

    if (in_array($perm, $user['perms'])) {
        return true;
    }

    return false;
}

function arr_multi2single($arr, $pre="")
{
    foreach ($arr as $k=>$v) {
        if (is_array($v)) {
            $arr=array_merge($arr, arr_multi2single($v, ($pre==""?"":$pre.".").$k));
            unset($arr[$k]);
        } elseif ($pre!="") {
            $arr[($pre==""?"":$pre.".").$k]=$v;
            unset($arr[$k]);
        }
    }
    return $arr;
}

function filter_array_params(&$arr, $filter)
{
    foreach ($arr as $k=>$v) {
        if (!in_array($k, $filter)) {
            unset($arr[$k]);
        }
    }
}

function randstr($length = 30, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function http($code = 204, $data = '', $json = false)
{
    http_response_code($code);
    if ($json) {
        echo json_encode($data);
    } else {
        echo $data;
    }
    exit;
}

function nullcheck($arr, $check)
{
    foreach ($check as $a) {
        if (!isset($arr[$a])) {
            return false;
        }
    }
    return true;
}

function is_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_phone($phone)
{
    $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
    $phone_to_check = str_replace("-", "", $filtered_phone_number);
    $phone_to_check = str_replace(" ", "", $phone_to_check);
    if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) {
        return false;
    }
    return true;
}

function password($password)
{
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);

    if (!$uppercase || !$lowercase || !$number || strlen($password) < 8) {
        return false;
    }
    return true;
}

function cors()
{
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        }
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        exit(0);
    }
}
