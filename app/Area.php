<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;

class Area extends Model
{
    use PostgisTrait;

    protected $fillable = [
        'user_id'
    ];

    protected $postgisFields = [
        'geom'
    ];

    protected $postgisTypes = [
        'geom' => [
            'geomtype' => 'geography',
            'srid' => 4326
        ]
        ];
}
