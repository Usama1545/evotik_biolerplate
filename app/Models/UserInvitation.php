<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model
{
    use HasFactory;

    protected $table = 'user_invitations';
    protected $fillable = ['user_id', 'valid_until'];

    protected $casts = [
        'branch_id' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }
}
