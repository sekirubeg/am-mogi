<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('requested_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('admins')
                ->onDelete('set null');

            $table->timestamp('requested_clock_in')->nullable();
            $table->timestamp('requested_clock_out')->nullable();

            $table->boolean(('review_status'))->default(false);
            $table->text('remarks')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('requested_at')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_requests');
    }
}
