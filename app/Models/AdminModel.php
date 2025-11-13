<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminModel extends Model
{
    use HasFactory;

    /**
     * Use the `admins` table instead of the default `admin_models`.
     *
     * @var string
     */
    protected $table = 'admins';

    /**
     * Fillable fields for mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'password',
    ];

    /**
     * Hide sensitive fields when serializing.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];
}
