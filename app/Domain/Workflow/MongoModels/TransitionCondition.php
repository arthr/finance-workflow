<?php

namespace App\Domain\Workflow\MongoModels;

use MongoDB\Laravel\Eloquent\Model;

class TransitionCondition extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'transition_conditions';

    protected $fillable = [
        'transition_id',
        'condition',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'condition' => 'array',
        'transition_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];
}
