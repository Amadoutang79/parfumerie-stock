<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 120);
            $table->string('tagline', 160)->nullable();
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('phone', 30);
            $table->string('tax_id', 80)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 120);
            $table->char('country', 2);
            $table->text('description')->nullable();
            $table->string('currency', 5)->default('FCFA');
            $table->string('currency_symbol', 12)->default('F');
            $table->decimal('vat_rate', 5, 2)->default(18);
            $table->string('timezone')->default('Africa/Bamako');
            $table->string('date_format', 10)->default('d/m/Y');
            $table->char('primary_color', 7)->default('#3B82F6');
            $table->string('invoice_email')->nullable();
            $table->boolean('email_notifications')->default(true);
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
