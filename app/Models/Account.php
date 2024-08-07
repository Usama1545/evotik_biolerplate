<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory, Filterable;

    protected $fillable = ['name', 'facility'];

    protected function casts(): array
    {
        return [
            'created_at' => 'date:Y-m-d H:i',
            'updated_at' => 'date:Y-m-d H:i',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
