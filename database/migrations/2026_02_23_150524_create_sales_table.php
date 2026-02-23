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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('sale_number');
            $table->date('sale_date');
            $table->string('customer_name')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('completed');
            $table->timestamps();

            $table->index('tenant_id');
            $table->unique(['tenant_id', 'sale_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
