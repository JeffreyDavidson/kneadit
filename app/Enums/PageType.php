<?php

namespace App\Enums;

enum PageType: string
{
    case Menu = 'menu';
    case Home = 'home';
    case About = 'about';
    case Reviews = 'reviews';
    case Order = 'order';
    case Track = 'track';
    case Contact = 'contact';
}
