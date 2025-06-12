<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Athlete extends Model
{
	protected $fillable = [
		'name',
		'start_no',
		'club',
		'category'
	];

	public function elements()
	{
		return $this->hasMany(Timer::class, 'athlete_id', 'id');
	}
}
