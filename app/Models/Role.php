<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends \Spatie\Permission\Models\Role
{
    use HasFactory;

    const ROLE_DOCTOR = 'doctor';

    const ROLE_PATIENT = 'patient';
}
