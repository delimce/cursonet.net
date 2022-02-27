<?php

namespace App\Repositories;

use App\Models\Cn2\Admin;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;

class AdminRepository
{

    public function getById(int $id): Admin
    {
        return Admin::find($id);
    }

    public function getAdminsByParams(array $params = []): Collection
    {
        return Admin::where($params)->get();
    }

    public function getAdminsByIdList(array $teacherIds):Collection
    {
        return Admin::whereIn('id', $teacherIds)->get();
    }
}
