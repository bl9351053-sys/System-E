<?php

// Deprecated placeholder: login history is handled in `System-E_Admin` when using the
// centralized `userevac_db`. This class remains to avoid breaking references in
// project code, but the migration in this repo is a no-op.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    // No-op stub; real login_history table is managed by System-E_Admin.
    protected $table = 'login_histories';
    public $timestamps = false;
}
