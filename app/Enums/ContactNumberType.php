<?php

namespace App\Enums;

enum ContactNumberType: string
{
    case MOBILE = 'mobile';
    case WHATSAPP = 'whatsapp';
    case LANDLINE = 'landline';
    case HOTLINE = 'hotline';
}