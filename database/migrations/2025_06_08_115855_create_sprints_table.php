<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('athlete_1');
			$table->unsignedBigInteger('athlete_2');
			$table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
//
//	    if (!Schema::hasColumn('timers', 'run_id'))
//	    {
//		    Schema::table('timers', function (Blueprint $table) {
//			    $table->unsignedBigInteger('run_id')->nullable()->after('athlete_id');
//			    $table->foreign('run_id')->references('id')->on('sprints')->onDelete('cascade');
//		    });
//	    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sprints');
    }
};
