<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'categories' => $categories
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
                'name' => 'required'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Campos incorrecto',
                    'errors' => $validate->errors()
                );
            } else {
                Category::create([
                    'name' => $params_array['name']
                ]);

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Categoria creada con exito'
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
        $category = Category::find($id)->load('post');

        if (is_object($category)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'category' => $category
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'La categoria no existe'
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
                'name' => 'required'
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
                unset($params_array['created_at']);

                $category=Category::where('id', $id)->updateOrCreate($params_array);
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'category' => $category
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No has enviado ninguna categoria.'
            );
        }
        return response()->json($data, $data['code']);
    }


    public function destroy($id)
    {
        //
    }
}
