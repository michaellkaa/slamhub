<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PerformerController extends Controller
{
public function index()
{
    return User::where('role', 'performer')->get(['id', 'name']);
}

}
