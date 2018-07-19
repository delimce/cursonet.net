<?php
namespace App\Models\Cn2;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Topic extends Model
{
    protected $table = 'tbl_contenido';
    protected $visible = array('id','autor','titulo','contenido','borrador','leido'); ///only fields that return

    public function course()
    {
        return $this->belongsTo('App\Models\Cn2\Course','curso_id');
    }

    public function createdAt(){
        return Carbon::parse($this->fecha_creado)->format(env('APP_DATEFORMAT'));
    }


}