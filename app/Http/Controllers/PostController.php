<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        $post = Post::all()->load('category');
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'post' => $post
        ]);
    }


    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $params_array = array_map('trim', $params_array);
            //limpiamos los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content'=>'required',
                'category_id' => 'required',
                'image' => 'required'

            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Campos incorrecto',
                    'errors' => $validate->errors()
                );
            } else {
                Post::create([
                    'user_id' => $params_array['user_id'],
                    'category_id' => $params_array['category_id'],
                    'title' => $params_array['title'],
                    'content' => $params_array['content'],
                    'image' => $params_array['image'],

                ]);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Post creado con exito.'
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


    public function show($id)
    {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'post' => $post
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El post no existe'
            );
        }
        return response()->json($data, $data['code']);
    }


    public function update(Request $request, $id)
    {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $params_array = array_map('trim', $params_array);
            //limpiamos los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content'=>'required',
                'category_id' => 'required'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Campos incorrecto',
                    'errors' => $validate->errors()
                );
            } else {

                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);

                $post=Post::where('id', $id)->updateOrCreate($params_array);
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'post' => $post
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No has enviado ningun post.'
            );
        }
        return response()->json($data, $data['code']);
    }


    public function destroy($id)
    {
        //
    }
}
