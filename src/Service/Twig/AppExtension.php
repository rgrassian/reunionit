<?php

namespace App\Service\Twig;

use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new \Twig_Filter('role_format', function ($role) {
                return ucfirst(strtolower(explode('_', $role)[1]));
            })
        ];
    }
}
