<?php

class Auth {
    public static function loggedIn(Request $request, array $roles) {
        $headers = $request->getHeaders();

        if (!isset($headers['Authorization'])) {
            http(401, "Ingen adgang");
        }

        foreach ($roles as $role) {
            if ($headers['Authorization'][0] == $role[0]) {
                $user = R::findOne($role, 'access_token=?', [$headers['Authorization']]);
                if (!$user) {
                    http(401, "Ingen adgang");
                }
                return ["user" => $user, 'usertype' => $role];
            }
        }

        http(401, "Ingen adgang");
    }
}