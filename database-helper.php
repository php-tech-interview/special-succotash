<?php

final class DatabaseHelper
{
    private static $connection;

    public static function createUser($login, $password)
    {
        if (!is_string($login) || !is_string($password)) {
            return 'Login and password should be string.';
        }

        if (strlen($login) < 3 || strlen($login) > 255) {
            return 'Login length should be greater than 3 and less than 255 symbols.';
        }

        if (strlen($password) < 6) {
            return 'Password length should be greater than 6 symbols.';
        }

        $user = self::executeQuery("INSERT INTO `users` (login, password) VALUES (:login, :password)", [
            'login' => $login,
            'password' => $password,
        ]);

        if (!$user) {
            return 'Cannot create user';
        }

        return (array) $user;
    }

    private static function executeQuery($sql, $params)
    {
        if (!is_array($params)) {
            return false;
        }

        return self::getConnection()->execute($sql, $params);
    }

    private static function getConnection()
    {
        if (null == self::$connection) {
            self::$connection = new Connection(
                config('db.host'),
                config('db.port'),
                config('db.user'),
                config('db.pass'),
                config('db.name')
            );
        }

        return self::$connection;
    }
}

final class UserController
{
    public function createAction(Request $request)
    {
        $user = DatabaseHelper::createUser($request->get('login'), $request->get('password'));

        if (is_string($user)) {
            return new Response('Error: ' . $user, 403);
        }

        return new JsonResponse([
            'id' => $user['id'],
            'login' => $user['login'],
            'created_at' => $user['created_at']
        ], 201);
    }
}
