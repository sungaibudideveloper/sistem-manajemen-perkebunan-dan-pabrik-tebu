<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class NavHint extends Component
{
    public $activePath;

    public function __construct($activePath = null)
    {
        $this->activePath = $activePath;
    }

    public function render()
    {
        return view('components.nav-hint');
    }
}
