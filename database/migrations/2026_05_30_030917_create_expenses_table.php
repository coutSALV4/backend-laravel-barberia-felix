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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registered_by')
                ->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')
                ->constrained('expense_categories')->restrictOnDelete();
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->timestamps();
            $table->softDeletes();

            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
