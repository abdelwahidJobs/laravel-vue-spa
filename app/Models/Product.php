<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    use Sluggable;

    protected $fillable = [
        'name',
        'price',
        'slug'
        ];


    public function sluggable(): array {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }


    public function getRouteKeyName()
    {
        return 'slug';
    }
}
