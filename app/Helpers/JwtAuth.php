<?php

namespace App\Helpers;

use App\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;

class JwtAuth
{
    public $key;

    /**
     * @return mixed
     */
    public function __construct()
    {
        $this->key = 'claveJwt';
    }

    public function signup($email, $password, $getToken = null)
    {
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

        if (is_object($user)) {
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $data = $decoded;
            }

        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto'
            );
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;
        try {
            $jwt=str_replace('"','',$jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $ex) {
            $auth = false;
        } catch (\DomainException $ex) {
            $auth = false;
        }
        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            $auth = $decoded;
        }

        return $auth;
    }


}