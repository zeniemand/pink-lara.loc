<?php

namespace Corp;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = ['title','img','alias','text','customer','filter_alias', 'meta_desc', 'keywords'];

    //Связь к модели Filter:
    public function filter(){
        return $this->belongsTo('Corp\Filter', 'filter_alias', 'alias');
    }
}
