<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ErpLayout extends Component
{
    public bool $isRtl;

    public string $dir;

    public function __construct()
    {
        $this->isRtl = is_rtl();
        $this->dir = text_dir();
    }

    public function render(): View|Closure|string
    {
        return view('layouts.erp');
    }
}
