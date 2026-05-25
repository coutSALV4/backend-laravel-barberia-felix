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
    Schema::create('media', function (Blueprint $table) {
        $table->id();
        $table->string('model_type');          
        $table->unsignedBigInteger('model_id');
        $table->string('collection');          
        $table->string('drive_file_id')->unique();
        $table->string('drive_url');
        $table->string('mime_type')->nullable();
        $table->string('filename');
        $table->unsignedInteger('size_bytes')->nullable();
        $table->timestamps();

        $table->index(['model_type', 'model_id', 'collection']);
    });
}

public function down(): void
{
    Schema::dropIfExists('media');
}
};
