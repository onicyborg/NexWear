<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'Admin';
    case Cutting = 'Cutting';
    case Sewing = 'Sewing';
    case QC = 'QC';
}
