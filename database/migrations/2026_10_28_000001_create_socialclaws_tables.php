<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id(); $table->string('full_name', 30); $table->string('email', 30)->unique();
                $table->string('password'); $table->text('address'); $table->rememberToken();
                $table->timestamp('created_at')->useCurrent();
            });
        } elseif (! Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', fn (Blueprint $table) => $table->rememberToken());
        }
        if (! Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id(); $table->string('username', 50)->unique(); $table->string('password');
            });
        }
        foreach (['services', 'gallery'] as $name) {
            if (! Schema::hasTable($name)) {
                Schema::create($name, function (Blueprint $table) {
                    $table->id(); $table->string('name', 30); $table->text('description');
                    $table->unsignedInteger('price'); $table->string('image');
                    $table->unsignedSmallInteger('duration_minutes')->default(60);
                    $table->string('category', 30)->default('signature');
                });
            } else {
                if (! Schema::hasColumn($name, 'duration_minutes')) {
                    Schema::table($name, fn (Blueprint $table) => $table->unsignedSmallInteger('duration_minutes')->default(60));
                }
                if (! Schema::hasColumn($name, 'category')) {
                    Schema::table($name, fn (Blueprint $table) => $table->string('category', 30)->default('signature'));
                }
            }
        }
        if (! Schema::hasTable('cart')) {
            Schema::create('cart', function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('user_id')->index(); $table->unsignedBigInteger('item_id');
                $table->enum('item_type', ['service', 'gallery']); $table->unsignedSmallInteger('quantity')->default(1);
                $table->timestamp('added_at')->useCurrent();
            });
        }
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('user_id')->index(); $table->string('user_name', 30);
                $table->unsignedTinyInteger('rating'); $table->text('review_text');
                $table->timestamp('created_at')->useCurrent();
            });
        }
        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('user_id')->index(); $table->string('customer_name', 30);
                $table->text('customer_address'); $table->text('booking_details'); $table->unsignedInteger('total_price');
                $table->timestamp('booking_time')->useCurrent();
            });
        }
        foreach (['appointment_date', 'appointment_time', 'phone', 'status'] as $column) {
            if (! Schema::hasColumn('bookings', $column)) {
                Schema::table('bookings', function (Blueprint $table) use ($column) {
                    match ($column) {
                        'appointment_date' => $table->date($column)->nullable(),
                        'appointment_time' => $table->string($column, 5)->nullable(),
                        'phone' => $table->string($column, 25)->nullable(),
                        'status' => $table->string($column, 20)->default('pending'),
                    };
                });
            }
        }
    }

    public function down(): void
    {
        // This adopts a legacy database. Rollback intentionally never removes customer data.
    }
};
