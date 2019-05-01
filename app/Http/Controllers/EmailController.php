<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;

class EmailController extends Controller
{

    public function contact(Request $request){
        $subject = "Asunto del correo";
        $for = "matito95@gmail.com";
        Mail::send('email',$request->all(), function($msj) use($subject,$for){
            $msj->from("jamatitodam218@iescastelar.com","Juan Angel Matito");
            $msj->subject($subject);
            $msj->to($for);
        });
        return redirect()->back();
    }
}
