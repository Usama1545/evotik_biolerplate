<?php

namespace App\Models;

use App\Models\Tenant\TenantGoodsModels\Shipping;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory, Filterable;

    protected $table = 'states';

    protected $fillable = [
        'name', 'country_id'
    ];
    public function country(){
        return $this->belongsTo(Country::class);
    }
    public function cities(){
        return $this->hasMany(City::class);
    }
}
