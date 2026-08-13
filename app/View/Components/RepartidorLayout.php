<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class RepartidorLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $eyebrow = null,
    ) {}

    public function render(): View
    {
        return view('layouts.repartidor');
    }
}
