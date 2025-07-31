<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'gender',
        'profile_image',
        'additional_file',
        'is_active',
        'merged_into',
    ];

    protected $dates = ['deleted_at'];

    public function customFields()
    {
        return $this->hasMany(ContactCustomField::class);
    }

    public function mergedInto()
    {
        return $this->belongsTo(Contact::class, 'merged_into');
    }
}
