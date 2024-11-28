<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/admin/dashboard'; // Default redirect for admin
    public const GURU_HOME = '/pengajar/dashboard';
    public const WALI_KELAS_HOME = '/wali-kelas/dashboard'; // Default redirect for wali kelas

    // ... rest of the provider code ...
}