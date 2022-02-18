<?php

namespace App;
use \tc_history;

use Illuminate\Database\Eloquent\Model;

class tc_variable_type extends Model
{
    protected $table = 'tc_variable_types';

    protected $fillable= [
    	'moneda',
        'desc'
    ];

    public function history(){
        return $this->hasMany(tc_history::class,'cuota_id');
    }
}
