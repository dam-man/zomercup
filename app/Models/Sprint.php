<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sprint extends Model
{
	protected $fillable = [
		'athlete_1',
		'athlete_2',
		'element',
	];

	protected $appends = ['element_text'];

	public function getElementTextAttribute(): float|string|null
	{
		if (is_null($this->element)) {
			return null;
		}
		return ucwords(str_replace('_', ' ', strtolower($this->element)));
	}

	public function athlete_one(): BelongsTo
	{
		return $this->belongsTo(Athlete::class, 'athlete_1', 'id');
	}

	public function athlete_two(): BelongsTo
	{
		return $this->belongsTo(Athlete::class, 'athlete_2', 'id');
	}
}
