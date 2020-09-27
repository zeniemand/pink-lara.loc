<?php

namespace Corp;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
    //protected $fillable = ['title','img','alias','text','desc','keywords','meta_desc','category_id'];
    protected $fillable = ['title','img','alias','text','desc','category_id'];

    /**
     * Метод установления связи один к одному с моделью User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(){
        return $this->belongsTo('Corp\User');
    }

    /**
     * Метод установления связи один к одному с моделью Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(){
        return $this->belongsTo('Corp\Category');
    }

    /**
     * Модель установления связи один ко многим с моделью Comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(){
        return $this->hasMany('Corp\Comment');
    }
}
