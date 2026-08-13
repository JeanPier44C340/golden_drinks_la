<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ProveedorLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $eyebrow = null,
    ) {}

    public function render(): View
    {
        return view('layouts.proveedor');
    }
}
