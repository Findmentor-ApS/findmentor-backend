<?php

class Auth {
    public static function loggedIn(Request $request) {
        $headers = $request->getHeaders();
        $roles = DI::env('USER_REGISTER_TYPES');
        if (!isset($headers['Access_token'])) {
            http(401, "Ingen adgang");
        }

        foreach ($roles as $role) 
        {
            $user = R::findOne($role, 'access_token=?', [$headers['Access_token']]);
            if($user) return ["user" => $user, 'usertype' => $role];;
        }   
        if (!$user) {
            http(401, "Ingen adgang");
        }

        http(401, "Ingen adgang");
    }
}