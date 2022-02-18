<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tc_history_detail extends Model
{
    protected $table = 'tc_history_details';
    protected $fillable= [
    	'history_id',
        'fecha',
        'tc_compra',
        'tc_venta'
    ];
}
