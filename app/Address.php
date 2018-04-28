<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'name', 'provence', 'city','area','detail_address','tel','user_id',
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
