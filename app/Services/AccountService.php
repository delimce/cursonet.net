<?php

namespace App\Services;

use App\Models\Cn2\GroupStudent;
use App\Repositories\AdminRepository;
use App\Repositories\StudentRepository;
use Illuminate\Database\Eloquent\Collection;

class AccountService
{
    public function __construct(
        private StudentRepository $studentRepository,
        private AdminRepository $adminRepository
    ) {
    }

    public function getUserContacts($studentId)
    {
        $est = $this->studentRepository->getStudentById($studentId);

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
            # bring only admins
            $tempAndmins = $this->adminRepository->getAdminsByParams([
                "es_admin" => 1
            ]);
            $teacher_array = $this->getIdsFromList($tempAndmins);
        }


        // @todo: replace with repository
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
        $teachers = $this->adminRepository->getAdminsByIdList($teacher_array);
        // get students
        $data_student = collect($data_student)->unique();
        //merge data (teachers & students)
        return array_merge($data_student->toArray(), $teachers->toArray());
    }

    /**
     * @param Collection $admins
     * @return array
     */
    protected function getIdsFromList(Collection $admins): array
    {
        return $admins->map(function ($admin) {
            return [
                $admin->id,
            ];
        })->toArray();
    }
}
