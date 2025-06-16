<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Psy\Util\Str;

class Timer extends Model
{
	protected $fillable = [
		'run_id',
		'athlete_id',
		'element',
		'lane',
		'start_time',
		'end_time',
		'duration',
		'start',
		'end',
		'total',
		'fastest',
		'points',
	];

	protected $appends = ['duration_text', 'element_text'];

	public function getElementTextAttribute(): float|string|null
	{
		return ucwords(str_replace('_', ' ', strtolower($this->element)));
	}

	public function getDurationTextAttribute(): float|string|null
	{
		$total = $this->total ?? 0;
		if ( ! $total) return null;

		$minutes          = floor($total / 60);
		$remainingSeconds = $total - ($minutes * 60);

		if ($total < 60)
		{
			$truncatedSeconds = floor($total * 100) / 100;
			return sprintf('%05.2f', $truncatedSeconds);
		}

		return sprintf('%02d:%05.2f', $minutes, $remainingSeconds);
	}

	public function athlete(): BelongsTo
	{
		return $this->belongsTo(Athlete::class);
	}
}
