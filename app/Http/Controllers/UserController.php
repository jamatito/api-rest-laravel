<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{

    public function register(Request $request)
    {
        //  $json=$request->input()->all();
        $json = $request->input('json', null);//es necesario poner como key 'json' en postman
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $params_array = array_map('trim', $params_array);
            //limpiamos los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                //unique busca en la bd si ya existe uno igual
                'password' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {

                User::create([
                    'name' => $params_array['name'],
                    'surname' => $params_array['surname'],
                    'role' => "ROLE_user",
                    'email' => $params_array['email'],
                    'password' => hash('sha256', $params_array['password'])
                ]);


                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado con exito'
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos'
            );

        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new JwtAuth();

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $validate = \Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto',
                'errors' => $validate->errors()
            );
        } else {
            $password = hash('sha256', $params_array['password']);
            $data = $jwtAuth->signup($params_array['email'], $password);
            if (!empty($params_array['gettoken'])) {
                $data = $jwtAuth->signup($params_array['email'], $password, true);
            }

        }
        return response()->json($data, 200);
    }

    public function update(Request $request)
    {
        $token = $request->header('Authorization', null);
        $jwtAuth = new JwtAuth();
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            $user = $jwtAuth->checkToken($token, true);

            $validate = \Validator::make($params_array, [
                'name' => 'required',
                'surname' => 'required|alpha',
                'email' => 'required|email'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Campos incorrecto',
                    'errors' => $validate->errors()
                );
            } else {

                $user1 = User::where('id', $user->sub)->first();

                if (!empty($user1) && is_object($user1)) {
                    unset($params_array['id']);
                    unset($params_array['role']);
                    unset($params_array['password']);
                    unset($params_array['created_at']);
                    unset($params_array['remember_token']);

                    $user1->update($params_array);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'user' => $user1
                    );
                } else {

                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No existe el usuario.'
                    );
                }
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'user' => $user1
                );
            }


        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Falta informacion'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        $image = $request->file('file0');
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,png,jpeg'
        ]);
        if (!$image || $validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al subir la imagen'
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();
            Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'status' => 'success',
                'code' => 200,
                'image' => $image_name
            );
        }
        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        $isset = Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El fichero no existe'
            );
            return response()->json($data, $data['code']);
        }
    }

    public function detail($id)
    {
        $user = User::find($id);
        if (is_object($user)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El usuario se ha actualizado con exito',
                'user' => $user
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Usuario no encontrado'
            );
        }
        return response()->json($data, $data['code']);
    }


}
