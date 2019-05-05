<?php

namespace App\Helpers;

use App\Post;
use App\User;
use Mail;

class EmailHelper
{

    /**
     * @return mixed
     */
    public function __construct()
    {

    }

    public function contact($nombre, $mensaje){
        $subject = "Nueva entrada";
        $for = "matito95@gmail.com";
        $datos = ['name' => $nombre, 'msg' => $mensaje];
        Mail::send('email',$datos, function($msj) use($subject,$for){
            $msj->from("jamatitodam218@iescastelar.com","Juan Angel Matito");
            $msj->subject($subject);
            $msj->to($for);
        });
        return redirect()->back();
    }

    public function newPost($titulo, $descripcion){
        $users = User::all();
        $subject = "No te pierdas nuestra nueva entrada: ".$titulo;
        $datos = ['titulo' => $titulo, 'descripcion' =>$descripcion];
        foreach ($users as $user)
        {
            $for = $user->email;
            Mail::send('newpost',$datos, function($msj) use($subject,$for){
                $msj->from("jamatitodam218@iescastelar.com","Blog de desarrollo web");
                $msj->subject($subject);
                $msj->to($for);
            });
        }
        return redirect()->back();
    }

    public function postCreator($userId, $postId,$content){
        $user = User::find($userId);
        $post = Post::find($postId)->load('user');
        $subject = "Un usuario ha comentado en tu entrada: ".$post->title;
        $datos = ['usuario' => $post->user->name, 'comment' =>$content, 'entrada'=>$post->title];
        $for = $user->email;
            Mail::send('postCreator',$datos, function($msj) use($subject,$for){
                $msj->from("jamatitodam218@iescastelar.com","Blog de desarrollo web");
                $msj->subject($subject);
                $msj->to($for);
            });
        return redirect()->back();
    }



}