<?php

namespace App\Repositories;

use App\Models\Cn2\Course;
use App\Models\Cn2\GroupStudent;
use App\Models\Cn2\AdminCourse;
use App\Models\Cn2\Group;
use DB;
use Illuminate\Support\Facades\Log;
use PDOException;

class CourseRepository
{

    /**
     * getCoursesByStudent
     *
     * @param  int $studentId
     * @return mixed
     */
    public function getCoursesByStudent($studentId)
    {
        return Course::whereHas('studentGroup', function ($query) use ($studentId) {
            $query->whereEstId($studentId);
        })->whereActivo(1)->get();
    }



    /**
     * getMainData
     *
     * @param  int $courseId
     * @param  int $studentId
     * @return string[]
     */
    public function getMainDataByStudent(int $courseId, int $studentId)
    {
        $course = Course::findOrFail($courseId);
        $estGroup = GroupStudent::where([
            "est_id" => $studentId,
            "curso_id" => $courseId,
        ])->first();

        $wallMessages = $course->walls()->wherein("grupo_id", ["0", $estGroup->grupo_id])->orderBy('fecha_c', 'desc')->get();
        $ntopics = $course->topics()->count();
        $data = [
            "alias" => $course->alias,
            "descripcion" => $course->descripcion,
            "id" => $course->id,
            "nombre" => $course->nombre,
            "init" => $course->createdAt(),
            "group" =>  $estGroup->group->nombre,
            "professor" => $course->author->fullName(),
            "duracion" => $course->duracion,
            "wall" => $wallMessages,
            "ntopics" => $ntopics,
        ];

        return $data;
    }



    /**
     * getCourseById
     *
     * @param  mixed $courseId
     * @return array|bool
     */
    public function getCourseById($courseId)
    {
        $data = false;
        $course = Course::find($courseId);
        if ($course) {
            $data = [
                "alias" => $course->alias,
                "desc" => $course->descripcion,
                "id" => $course->id,
                "name" => $course->nombre,
                "init" => $course->createdAt(),
                "author" => $course->author->fullname(),
                "long" => $course->duracion,
                "ntopics" => $course->topics()->count(),
            ];
        }
        return $data;
    }


    /**
     * enrollInCourse
     * automatic enroll student in course
     * @param  int $courseId
     * @param  int $studentId
     * @return bool
     */
    public function enrollInCourse($courseId, $studentId)
    {
        $result = true;
        try {
            DB::transaction(function () use ($courseId, $studentId) {
                //create o retrieve new course for course
                $group = Group::firstOrCreate(['curso_id' => $courseId, 'nombre' => 'auto', 'turno' => 0]);
                // enroll student to course
                GroupStudent::create([
                    "curso_id" => $courseId,
                    "est_id" => $studentId,
                    "grupo_id" => $group->id,
                ]); //
            });
        } catch (PDOException $ex) {
            Log::error($ex);
            $result = false;
        } finally {
            return $result;
        }
    }


    /**
     * getAdminsByCourseId
     *
     * @param  int $courseId
     * @return void
     */
    public function getAdminsByCourseId($courseId)
    {
        return  AdminCourse::with('admin')->whereCursoId($courseId)->get();
    }



    /**
     * getPublicCoursesByStudent
     *
     * @param  int $studentId
     * @return array
     */
    public function getPublicCoursesByStudent($studentId)
    {
        $publics = Course::wherePublico(1)->whereActivo(1)->get();
        $availables = [];
        if (count($publics) > 0) {
            $studentCourses = $this->getCoursesByStudent($studentId);
            $availables = $publics->filter(function ($course) use ($studentCourses) {
                if (!$studentCourses->contains('id', $course->id)) {
                    return $course;
                }
            })->map(function ($course) {
                return [
                    "id" => $course->id,
                    "name" => $course->nombre,
                    "alias" => $course->alias,
                ];
            });
        }
        return $availables;
    }
}
