<?php

namespace App\Enums;

enum GalleryCategory: string
{
    case Products = 'products';
    case Bakery = 'bakery';
    case Team = 'team';
    case Events = 'events';
    case Process = 'process';
    case Other = 'other';
}
