<?php

namespace App\Domain\Workflow\MongoModels;

use MongoDB\Laravel\Eloquent\Model;

class StageConfig extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'stage_configs';

    protected $fillable = [
        'stage_id',
        'config',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'config' => 'array',
        'stage_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];
}
