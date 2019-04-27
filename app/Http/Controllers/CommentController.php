<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getCommentsByPost', 'getPostsByUser']]);
    }

    public function index()
    {
        $comments = Comment::all()->load('user');
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'comments' => $comments
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
                'content' => 'required',
                'user_id' => 'required',
                'post_id' => 'required',
                'approved' => 'required'

            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Campos incorrecto',
                    'errors' => $validate->errors()
                );
            } else {
                Comment::create([
                    'content' => $params_array['content'],
                    'user_id' => $params_array['user_id'],
                    'post_id' => $params_array['post_id'],
                    'approved' => $params_array['approved']
                ]);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Comment creado con exito.'
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
        $comment = Comment::find($id)->load('user');

        if (is_object($comment)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'post' => $comment
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El comment no existe'
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

            $validate = \Validator::make($params_array, [
                'content' => 'required',
                'user_id' => 'required',
                'post_id' => 'required',
                'approved' => 'required'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Campos incorrecto',
                    'errors' => $validate->errors()
                );
            } else {

                $comment = Comment::where('id', $id)->where('user_id', $user->sub)->first();

                if (!empty($comment) && is_object($comment)) {
                    unset($params_array['id']);
                    unset($params_array['created_at']);

                    $comment->update($params_array);
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'comment' => $comment
                    );
                } else {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No existe el comment.'
                    );
                }

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'comment' => $comment
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No has enviado ningun comment.'
            );
        }
        return response()->json($data, $data['code']);
    }


    public function destroy($id, Request $request)
    {
        $user = $this->getJwtToken($request);
        $comment = Comment::where('id', $id)->where('user_id', $user->sub)->first();

        if (!empty($comment)) {
            $comment->delete();
            $data = array(
                'status' => 'success',
                'code' => 200,
                'comment' => $comment
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No existe el comment.'
            );
        }

        return response()->json($data, $data['code']);
    }

    private function getJwtToken(Request $request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }


    public function getCommentsByPost($id)
    {
        $comments = Comment::where('post_id', $id)->get();
        return \response()->json([
            'status' => 'success',
            'comments' => $comments
        ], 200);
    }

    public function getCommentsByUser($id)
    {
        $comments = Comment::where('user_id', $id)->get();
        return \response()->json([
            'status' => 'success',
            'comments' => $comments
        ], 200);
    }

}

