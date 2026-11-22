<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestAudioRecord extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];
}
