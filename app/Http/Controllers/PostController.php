<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImage', 'getPostsByCategory', 'getPostsByUser']]);
    }

    public function index()
    {
        $posts = Post::all()->load('category');
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'posts' => $posts
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
                'content' => 'required',
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
        $user = $this->getJwtToken($request);
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $params_array = array_map('trim', $params_array);
            //limpiamos los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
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

                $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

                if (!empty($post) && is_object($post)) {
                    unset($params_array['id']);
                    unset($params_array['user_id']);
                    unset($params_array['created_at']);

                    $post->update($params_array);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'post' => $post
                    );
                } else {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No existe el post.'
                    );
                }


                /*    $where=[
                      'id' => $id,
                        'user_id' => $user->sub
                    ];

                    $post = Post::updateOrCreate($where,$params_array);*/
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


    public function destroy($id, Request $request)
    {
        $user = $this->getJwtToken($request);
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

        if (!empty($post)) {
            $post->delete();
            $data = array(
                'status' => 'success',
                'code' => 200,
                'post' => $post
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No existe el post.'
            );
        }

        return response()->json($data, $data['code']);
    }

    private function getJwtToken(Request $request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Autorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
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
            Storage::disk('images')->put($image_name, \File::get($image));

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
        $isset = Storage::disk('images')->exists($filename);
        if ($isset) {
            $file = Storage::disk('images')->get($filename);
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

    public function getPostsByCategory($id)
    {
        $post = Post::where('category_id', $id)->get();

        return \response()->json([
            'status' => 'success',
            'posts' => $post
        ], 200);
    }

    public function getPostsByUser($id)
    {
        $post = Post::where('user_id', $id)->get();

        return \response()->json([
            'status' => 'success',
            'posts' => $post
        ], 200);
    }
}

