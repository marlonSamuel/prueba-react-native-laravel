<?php

namespace App;
use App\tc_variable_type;
use App\tc_history_detail;

use Illuminate\Database\Eloquent\Model;

class tc_history extends Model
{
    protected $table = 'tc_histories';
    protected $fillable= [
    	'variable_type_id',
        'inicio',
        'fin',
        'peticion',
        'prom_tc_compra',
        'prom_tc_venta'
    ];

    public function tipo_variable()
    {
        return $this->belongsTo(tc_variable_type::class,'variable_type_id');
    }

    public function detail()
    {
        return $this->hasMany(tc_history_detail::class,'history_id');
    }
}
