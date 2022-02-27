<?php

/**
 * Created by PhpStorm.
 * User: Luis De Lima
 * Date: 23-Jun-18
 * Time: 9:13 PM
 */

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Repositories\StudentRepository;
use App\Services\AccountService;
use Validator;

class AccountController extends BaseController
{
    private $student;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct(
        Request $req,
        private StudentRepository $studentRepository,
        private AccountService $accountService
    ) {
        $token = $req->header('Authorization');
        $this->student = $this->studentRepository->getUserByToken($token);
    }


    /**get student's contacts
     * @return mixed
     */
    public function getContacts()
    {
        $estId = $this->student->id;
        $contacts = $this->accountService->getUserContacts($estId);
        if (!$contacts) {
            $error = __('students.user.notfound');
            return response()->json(['status' => 'error', 'message' => $error], 400);
        }
        return response()->json(['status' => 'ok', 'contacts' => $contacts]);
    }


    /**set value of account setting
     * @param Request $req
     * @return mixed
     */
    public function setSetting(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'field' => 'required',
            'status' => 'required'
        ], [
            'required' => __('commons.validation.required')
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'error', 'message' => $error], 400);
        }
        $status = (boolval($req->status)) ? 1 : 0;
        $field = $req->field;

        try {
            $this->student->update(["$field" => $status]);
            return response()->json(['status' => 'ok', 'message' => __('students.profile.setting.success')]);
        } catch (Exception $ex) {
            return response()->json(['status' => 'error', 'message' => "setting do not exists"], 500);
        }
    }


    public function changePassword(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'pass' => 'required|min:5',
            'pass2' => 'required|min:5'
        ], [
            'required' => __('commons.validation.required'),
            'min' => __('commons.validation.min'),
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'error', 'message' => $error], 400);
        }

        if ($req->input('pass') === $req->input('pass2')) {
            $this->student->pass = Hash::make($req->input('pass'));
            $this->student->save();
            return response()->json(['status' => 'ok', 'message' => __('students.profile.change.password.success')]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('students.profile.change.password.nomatch')], 400);
        }
    }


    public function saveProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id_number' => 'required|min:5',
            'nombre' => 'required|min:2',
            'apellido' => 'required|min:3',
            'sexo' => 'required',
            'fecha_nac' => 'required|date',
            'email' => 'required|email',
        ], [
            'required' => __('commons.validation.required'),
            'min' => __('commons.validation.min'),
            'email' => __('commons.validation.email'),
            'date' => __('commons.validation.date'),
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'error', 'message' => $error], 400);
        }

        //if email or ci exists
        $exist = $this->studentRepository
            ->isEmailOrIdNumberExist($this->student->id, $req->input('email'), $req->input('id_number'));

        if ($exist) {
            return response()->json(['status' => 'error', 'message' => __('students.profile.change.exist')], 400);
        }

        $this->student->id_number = $req->input('id_number');
        $this->student->nombre = $req->input('nombre');
        $this->student->apellido = $req->input('apellido');
        $this->student->sexo = $req->input('sexo');
        $this->student->fecha_nac = $req->input('fecha_nac');
        $this->student->email = $req->input('email');
        if ($req->has('telefono'))
            $this->student->telefono_p = $req->input('telefono');
        if ($req->has('foto')) //if has base64 picture
            $this->student->foto = $req->input('foto');
        $this->student->save();

        return response()->json(['status' => 'ok', 'message' => __('students.profile.data.save')]);
    }



    public function sendSupportMessage(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'contenido' => 'required|min:5',
        ], [
            'required' => __('commons.validation.required'),
            'min' => __('commons.validation.min'),
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => 'error', 'message' => $error], 400);
        }

        $data["studentId"] = $this->student->id;
        $data["message"] = $req->contenido;
        $this->studentRepository->techSupportMessage($data);

        return response()->json(['status' => 'ok', 'message' => __('commons.message.sent')]);
    }
}
