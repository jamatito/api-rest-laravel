<?php

namespace App\Helpers;

use App\Post;
use App\User;
use App\Comment;
use Mail;

class EmailHelper
{

    /**
     * @return mixed
     */
    public function __construct()
    {

    }

    public function newPost($titulo, $descripcion)
    {
        $users = User::all();
        $subject = "No te pierdas nuestra nueva entrada: " . $titulo;
        $datos = ['titulo' => $titulo, 'descripcion' => $descripcion];
        foreach ($users as $user) {
            $for = $user->email;
            Mail::send('newpost', $datos, function ($msj) use ($subject, $for) {
                $msj->from("jamatitodam218@iescastelar.com", "Blog de desarrollo web");
                $msj->subject($subject);
                $msj->to($for);
            });
        }
        return redirect()->back();
    }

    public function postCreator($userId, $postId, $content)
    {
        $user = User::find($userId);
        $post = Post::find($postId)->load('user');
        $subject = "Un usuario ha comentado en tu entrada: " . $post->title;
        $datos = ['usuario' => $user->name, 'comment' => $content, 'entrada' => $post->title];
        $for = $post->user->email;
        Mail::send('postCreator', $datos, function ($msj) use ($subject, $for) {
            $msj->from("jamatitodam218@iescastelar.com", "Blog de desarrollo web");
            $msj->subject($subject);
            $msj->to($for);
        });
        return redirect()->back();
    }

    public function postAnidate($userId, $postId, $content)
    {
        $user = User::find($userId);
        $comments = Comment::where('post_id', $postId)->groupBy('user_id')->get()->load('user');
        $post = Post::find($postId)->load('user');
        $subject = "Un usuario ha respondido a tu comentario en la entrada: " . $post->title;
        $datos = ['usuario' => $user->name, 'comment' => $content, 'entrada' => $post->title];
        foreach ($comments as $comment) {
            if ($comment->user->id != $user->id && $post->user->id != $comment->user->id) {
                $for = $comment->user->email;
                Mail::send('commentAnidate', $datos, function ($msj) use ($subject, $for) {
                    $msj->from("jamatitodam218@iescastelar.com", "Blog de desarrollo web");
                    $msj->subject($subject);
                    $msj->to($for);
                });
            }
        }
        return redirect()->back();
    }


}