<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    function registerUser(Request $req) {
      $message = "User registered successfully!";

      $validator = Validator::make($req->all(), [
          'username' => 'required|unique:users,username|max:255',
          'email' => 'required|email',
          'password' => 'required|max:255'
      ]);

      if ($validator->fails()) {
        return redirect('/register')
                    ->withErrors($validator)
                    ->withInput();
      }

      $u = User::where("username", "=", $req->get("username"))->get();
      if(count($u) > 0) {
        $message = "The selected username is invalid!";
      } else {
        $new_user = new User();

        $new_user->username = $req->get("username");
        $new_user->email = $req->get("email");
        $new_user->password_hash = md5($req->get("password"));

        $new_user->save();
      }

      return view("website.register", ["message" => $message]);
    }

    function osuRegisterUser(Request $req) {
      $validator = Validator::make($req->all(), [
          'user.username' => 'required|unique:users,username|max:255',
          'user.user_email' => 'required|email|unique:users,email',
          'user.password' => 'required|max:255',
          'check' => 'required|boolean'
      ]);

      if ($validator->fails()) {
        $errors = array();
        $errors["form_error"] = array();

        $error = array_keys($validator->errors()->messages())[0];
        $msg = $validator->errors()->messages()[$error];
        if (strpos($error, '.') !== false) {
          $e = explode(".", $error);

          if(strpos($e[0], 'user_') !== false) {
            $e[0] = explode($e[0], 'user_')[1];
          }

          $err = [
            $e[0] => [
              $e[1] => array($msg[0])
            ]
          ];

          $errors["form_error"] = $err;
        } else {
          $err = [
            $error => array($msg[0])
          ];

          $errors["form_error"] = $err;
        }

        return response()->json($errors, 422);
      }

      if($req->get("check") == 1) return "";

      $new_user = new User();

      $new_user->username = ((object)$req->get("user"))->username;
      $new_user->email = ((object)$req->get("user"))->user_email;
      $new_user->password_hash = md5(((object)$req->get("user"))->password);

      $new_user->save();

      return "ok";
    }
}
