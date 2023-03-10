<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CountNutritionsExtension extends AbstractExtension
{
    public function getFilters() : array
    {
        return [
            new TwigFilter('count_nutrition', [$this, 'countNutritions']),
        ];
    }

    function sumObject($object) {
        $sum = 0;
        foreach ($object as $key => $value) {
            $sum += $value;
        }
        return $sum;
    }

    public function countNutritions($value)
    {
        $sum = $this->sumObject(json_decode($value , true));
        return $sum;
    }
}
