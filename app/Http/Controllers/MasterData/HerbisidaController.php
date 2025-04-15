<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class HerbisidaController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'Herbisida',
            'routeName' => route('master.herbisida.index'),
        ]);
    }