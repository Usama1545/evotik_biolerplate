<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Spatie\Translatable\HasTranslations;

class Language extends Model
{
    use HasFactory, HasTranslations;

    public $dropdownText = 'iso_2';
    public $translatable = ['name'];

    protected $fillable = ['name', 'is_common', 'iso_2'];

    public $appends = ['flag'];

    protected $casts = ['is_common' => 'boolean'];

    public function getFlagAttribute()
    {
        return URL::to("/assets/images/flags/$this->iso_2.png");
    }

    public function scopeCommon()
    {
        return $this->where('is_common', true);
    }
}
