<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'profile_image',
        'additional_file',
        'user_id',
    ];

    public function customFields()
    {
        return $this->hasMany(ContactCustomField::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
