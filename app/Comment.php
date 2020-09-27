<?php

namespace Corp;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    protected $fillable = ['name','text','site','user_id','article_id','parent_id', 'email'];

    /**
     * Метод установления связи один к одному с моделью Article
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function article(){
        return $this->belongsTo('Corp\Article');
    }

    /**
     * Метод установления связи один к одному с моделью User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(){
        return $this->belongsTo('Corp\User');
    }
}
