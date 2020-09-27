<?php

namespace Corp;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{

    /**
     * Ответная связь один ко многим с моделью Portfolio
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function portfolios()
    {
        return $this->hasMany('\Corp\Portfolio');
    }
}
