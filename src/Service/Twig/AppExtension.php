<?php

namespace App\Service\Twig;

use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new \Twig_Filter('role_format', function ($role) {
                switch ($role) {
                    case 'ROLE_ADMIN':
                        return 'Administrateur';
                        break;
                    case 'ROLE_EMPLOYEE':
                        return 'Salarié';
                        break;
                }
            }),
            new \Twig_Filter('role_formatPicto', function ($role) {
                switch ($role) {
                    case 'ROLE_ADMIN':
                        return '<i class="fas fa-user-ninja"></i>';
                        break;
                    case 'ROLE_EMPLOYEE':
                        return '<i class="fas fa-user"></i>';
                        break;
                }
            }),
        ];
    }
}
