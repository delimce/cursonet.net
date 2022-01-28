<?php

namespace App\Http\Controllers\Api;

use DB;
use Carbon\Carbon;
use ReCaptcha\ReCaptcha;
use App\Models\Cn2\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Cn2\StudentLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Repositories\StudentRepository;
use Exception;
use Laravel\Lumen\Routing\Controller as BaseController;

class StudentController extends BaseController
{
    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        //  $this->middleware('auth:api');
    }

    public function login(Request $req)
    {
        $this->validate($req, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = Student::select("id", "nombre", "apellido", "id_number", "email", "foto", "activo", "pass", "token")->where('email', $req->input('email'))->first();
        if (is_null($user))
            return response()->json(['status' => 'error', 'message' => trans('students.login.email.unknown')], 401);

        if (!$user->activo) {
            return response()->json(['status' => 'fail', 'message' => trans('students.login.signin.active.error')], 401);
        } else if (Hash::check($req->input('password'), $user->pass)) {
            DB::beginTransaction();
            $user->token = StudentRepository::userNewToken();
            $user->save();
            $log = new StudentLog();
            $log->est_id = $user->id;
            $log->ip_acc = $_SERVER['REMOTE_ADDR'];
            $log->info_cliente = $_SERVER['HTTP_USER_AGENT'];
            $log->fecha_in = Carbon::now();
            $log->save();
            DB::commit();
            return response()->json(['status' => 'ok', 'user' => $user]);
        } else {
            return response()->json(['status' => 'fail', 'message' => trans('students.login.password.error')], 401);
        }
    }


    public function forgotten(Request $req)
    {
        $this->validate($req, [
            'email' => 'required|email'
        ]);

        $user = Student::where('email', $req->input('email'))->first();

        if (!is_null($user)) {
            $apikey = StudentRepository::userNewToken();
            $user->token = $apikey;
            $user->save();
            Mail::send('student.emails.forgotten', ["user" => $user, "token" => $apikey], function ($m) use ($user) {
                $m->to($user->email, $user->nombre . ' ' . $user->apellido)->subject('Recordatorio de contraseña');
            });
            return response()->json(['status' => 'ok', 'message' => trans('students.login.password.restore', ['email' => $user->email])]);
        } else {
            return response()->json(['status' => 'error', 'message' => trans('students.login.email.unknown')], 401);
        }
    }


    public function restorePassword(Request $req)
    {

        $this->validate($req, [
            'token' => 'required',
            'pass' => 'required'
        ]);

        $user = Student::where('token', $req->input('token'))->first();
        if (!is_null($user)) {
            $apikey = StudentRepository::userNewToken();
            $newPass = Hash::make($req->input('pass'));
            Student::where('token', $req->input('token'))->update(['token' => "$apikey", "pass" => $newPass]);

            return response()->json(['status' => 'ok', 'message' => trans('login.reset_password.success')]);
        } else {
            return response()->json(['status' => 'error', 'message' => trans('students.token_unknown')], 401);
        }
    }


    public function checkRecaptcha()
    {
        $recaptcha = new ReCaptcha(env('RECAPTCHA_SECRET_KEY'));
        $resp      = $recaptcha->setExpectedHostname($_SERVER['HTTP_HOST'])->verify($_POST["g-recaptcha-response"], $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess()) {
            // code for processing your form goes here
        } else {
            // code for showing an error message goes here
            $errors = $resp->getErrorCodes();
        }
    }

    public function signUp(Request $req)
    {

        $this->validate($req, [
            'email' => 'required|email',
            'nombre' => 'required',
            'apellido' => 'required',
            'id_number' => 'required',
            'fecha_nac' => 'required',
            'pass' => 'required'
        ]);

        //recaptcha
        if (!empty($_POST['g-recaptcha-response'])) {
            $recaptcha = new ReCaptcha(env('RECAPTCHA_SECRET_KEY'));
            $resp      = $recaptcha->setExpectedHostname($_SERVER['HTTP_HOST'])->verify($_POST["g-recaptcha-response"], $_SERVER['REMOTE_ADDR']);
            if (!$resp->isSuccess()) {
                return response()->json(['status' => 'error', 'message' => 'recaptcha not valid'], 422);
                $errors = $resp->getErrorCodes();
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'recaptcha not valid'], 422);
        }

        $user = Student::where('email', $req->input('email'))->orWhere('id_number', $req->input('id_number'))->first();

        if (is_null($user)) {

            $student = new Student();
            $student->nombre = $req->input('nombre');
            $student->apellido = $req->input('apellido');
            $student->id_number = $req->input('id_number');
            $student->email = $req->input('email');
            $student->fecha_nac = $req->input('fecha_nac');
            $student->pass = Hash::make($req->input('pass'));
            $student->token = StudentRepository::userNewToken(); ///for email
            $student->save();

            Mail::send("student.emails.registered", ["user" => $student, "token" => $student->token], function ($m) use ($student) {

                $m->to($student->email, $student->nombre . " " . $student->apellido)->subject('Usuario registrado en Cursonet');
            });

            return response()->json(['status' => 'ok', 'message' => trans('students.register.success')]);
        } else {
            return response()->json(['status' => 'error', 'message' => trans('students.register.already')], 422);
        }
    }
}
