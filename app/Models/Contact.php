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
        'merged_into',
        'is_active',
    ];

    public function customFields()
    {
        return $this->hasMany(ContactCustomField::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mergedInto()
    {
        return $this->belongsTo(Contact::class, 'merged_into');
    }
}
