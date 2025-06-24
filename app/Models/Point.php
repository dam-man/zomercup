<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Point extends Model
{
    protected $fillable = [
		'athlete_id',
		'timer_id',
		'points',
	];

	protected $casts = [
		'points' => 'float',
	];

	public function athlete(): BelongsTo
	{
		return $this->belongsTo(Athlete::class);
	}

	public function timer(): BelongsTo
	{
		return $this->belongsTo(Timer::class);
	}
}
