<?php

namespace App\Repositories;

use App\Models\Cn2\Admin;
use App\Models\Cn2\GroupStudent;
use DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Cn2\StudentLog;
use App\Models\Cn2\Student;
use App\Models\Cn2\TechSupport;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Ramsey\Uuid\Uuid;

class StudentRepository
{

    const PREF_STUDENT = 'est';

    /**
     * doLogin
     *
     * @param  string $email
     * @param  string $password
     * @return array
     */
    public function doLogin($email, $password)
    {
        // student doesn't exist
        $result = ["ok" => false, "message" => ""];
        $user = Student::whereEmail($email)->first();

        if (is_null($user)) {
            $result["message"] = __('students.login.email.unknown');
            return $result;
        }

        // student is not active
        if (!$user->activo) {
            $result["message"] = __('students.login.signin.active.error');
            return $result;
        }

        if (!Hash::check($password, $user->pass)) {
            $result["message"] = __('students.login.password.error');
            return $result;
        }

        DB::beginTransaction();

        $log = new StudentLog();
        $log->est_id = $user->id;
        $log->ip_acc = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';
        $log->info_cliente = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $log->fecha_in = Carbon::now();
        $log->save();

        ///new login
        $user->token = static::userNewToken();
        $user->save();
        DB::commit();

        $result["ok"] = true;
        $result["user"] = $user;
        $result["userLog"] = $log->id;

        return $result;
    }


    /**
     * activeUser
     * active new user
     * @param  string $token
     * @return bool|mixed
     */
    public function activeUser($token)
    {
        $user = Student::where('token', $token)->first();
        if (!is_null($user)) {
            $newApikey = static::userNewToken();
            Student::where('email', $user->email)->update(['token' => $newApikey, "activo" => 1]);
            return $user;
        }
        return false;
    }


    /**
     * isTokenActive
     *
     * @param  string $token
     * @return bool
     */
    public static function isTokenActive($token)
    {
        $user = Student::where("token", $token)->where("activo", 1)->first();
        return !is_null($user);
    }


    /**
     * getUserByToken
     * @param  string $token
     * @return mixed|bool
     */
    public function getUserByToken($token)
    {
        $user = Student::where("token", $token)->where("activo", 1)->first();
        if (!is_null($user)) {
            return $user;
        }
        return false;
    }


    /**
     * userNewToken
     * get new student token
     * @return string
     */
    public static function userNewToken()
    {
        $uuid = Uuid::uuid6();
        return $uuid->toString();
    }

    /**
     * getStudentById
     *
     * @param  int $studentId
     * @return Collection
     */
    public function getStudentById($studentId)
    {
        $student = Student::find($studentId);
        return ($student) ? $student->makeHidden(['token', 'foto']) : null;
    }


    /**
     * getPlansByStudentId
     *
     * @param  int $studentId
     * @return Collection
     */
    public function getPlansByStudentId($studentId)
    {
        $plans = DB::table('tbl_plan_evaluador')
            ->join('tbl_grupo_estudiante', 'tbl_plan_evaluador.grupo_id', '=', 'tbl_grupo_estudiante.grupo_id')
            ->join('tbl_grupo', 'tbl_grupo.id', '=', 'tbl_grupo_estudiante.grupo_id')
            ->join('tbl_curso', 'tbl_curso.id', '=', 'tbl_grupo.curso_id')
            ->join('tbl_plan_item', 'tbl_plan_evaluador.id', '=', 'tbl_plan_item.plan_id')
            ->groupBy('plan_id')
            ->select('tbl_plan_evaluador.*', 'tbl_grupo.nombre as grupo', 'tbl_curso.alias as curso')
            ->selectRaw('count(tbl_plan_item.id) as items')
            ->whereEstId($studentId)
            ->get();

        return $plans;
    }



    /**
     * isEmailOrIdNumberExist
     *
     * @param  int $studentId
     * @param  string $email
     * @param  int $idnumber
     * @return bool
     */
    public function isEmailOrIdNumberExist($studentId, $email, $idnumber)
    {
        //if email or ci exists
        $exist = Student::where("id", "!=", $studentId)->where(function ($query) use ($email, $idnumber) {
            $query->where("email", $email)
                ->orWhere("id_number", $idnumber);
        })->count();

        return ($exist > 0);
    }


    /**
     * getUserContacts
     * @deprecated
     * @param  int $studentId
     * @return array|bool
     */
    public function getUserContacts($studentId)
    {
        $est = Student::find($studentId);

        if (is_null($est)) {
            return false;
        }

        ///student's groups
        $groups = $est->groups()->with('group')->get();
        $teacher_array = [];
        $group_array = [];


        # has groups almost 1
        if ($groups->count()) {
            $groups->each(function ($value) use (&$group_array, &$teacher_array) {
                $group_array[] = $value->grupo_id;
                $tempAdmins = $value->group->course->admins()->get();
                // obtaining admin ids of student courses
                $tempAdmins->pluck('admin_id')->each(function ($adminId) use (&$teacher_array) {
                    $teacher_array[] = $adminId;
                });
            });
        } else {
            dd("fail");
        }
       

        $students = GroupStudent::with(['Student' => function ($q) use ($studentId) {
            // Query the name field in status table
            $q->where('id', '!=', $studentId)->where("share_info", 1);
        }])->whereIn('grupo_id', $group_array)->get();

        $data_student = [];

        $students->each(function ($item) use (&$data_student) {
            if ($item->Student != null) $data_student[] = $item
                ->Student->only(['id', 'nombre', 'apellido', 'email', 'foto', 'sexo', 'fecha_nac', 'telefono_p']);
        });

        ///get teachers
        $teachers = Admin::whereIn('id', $teacher_array)->get();
        // get students
        $data_student = collect($data_student)->unique();
        //merge data (teachers & students)
        return array_merge($data_student->toArray(), $teachers->toArray());
    }


    public function techSupportMessage($data)
    {

        TechSupport::create([
            'persona_id' => $data['studentId'],
            'tipo' => self::PREF_STUDENT,
            'titulo' => __('students.support.request'),
            'contenido' => $data['message'],
        ]);
        //todo: send email to tech support
    }
}
