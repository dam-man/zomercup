<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('athletes', function (Blueprint $table) {
			$table->id();
			$table->string('name', 150);
			$table->integer('start_no')->unique();
			$table->string('club', 25)->nullable();
			$table->string('category', 10)->nullable();
			$table->timestamps();
		});

		Schema::create('timers', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('run_id')->nullable();
			$table->unsignedBigInteger('athlete_id')->index();
			$table->string('element', 50);
			$table->unsignedBigInteger('start')->nullable();
			$table->unsignedBigInteger('end')->nullable();
			$table->float('total', 8, 2)->nullable();
			$table->timestamp('start_time')->nullable();
			$table->timestamp('end_time')->nullable();
			$table->integer('duration')->nullable();
			$table->timestamps();

			$table->foreign('athlete_id')->references('id')->on('athletes')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('timers');
		Schema::dropIfExists('athletes');
	}
};
