<?php

namespace App\Helpers;

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
        $subject = "No te pierdas nuestra nueva entrada: ".$titulo;
        $for = "matito95@gmail.com";
        $datos = ['titulo' => $titulo, 'descripcion' =>$descripcion];
        Mail::send('newpost',$datos, function($msj) use($subject,$for){
            $msj->from("jamatitodam218@iescastelar.com","Blog de desarrollo web");
            $msj->subject($subject);
            $msj->to($for);
        });
        return redirect()->back();
    }



}