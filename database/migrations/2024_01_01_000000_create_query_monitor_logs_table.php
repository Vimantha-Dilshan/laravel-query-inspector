<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('query_monitor_logs', function (Blueprint $table): void {
            $table->id();
            $table->text('sql');
            $table->json('bindings')->nullable();
            $table->decimal('execution_time', 10, 4)->default(0)->comment('Execution time in milliseconds');
            $table->string('connection', 50)->nullable();
            $table->string('query_type', 20)->nullable();
            $table->string('route', 255)->nullable();
            $table->string('controller', 500)->nullable();
            $table->text('request_url')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('environment', 50)->nullable();
            $table->string('request_id', 64)->nullable();
            $table->boolean('is_slow')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('is_slow');
            $table->index('query_type');
            $table->index('route');
            $table->index('user_id');
            $table->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('query_monitor_logs');
    }
};
