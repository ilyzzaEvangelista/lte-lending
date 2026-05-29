<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('first_name', 60)->nullable()->after('name');
                $table->string('last_name', 60)->nullable()->after('first_name');
                $table->string('username', 60)->nullable()->after('last_name');
                $table->string('contact', 30)->nullable()->after('username');
                $table->string('address', 255)->nullable()->after('contact');
                $table->unsignedTinyInteger('age')->nullable()->after('address');
                $table->string('gender', 32)->nullable()->after('age');
                $table->unsignedTinyInteger('status')->default(1)->after('gender');
                $table->unique('username');
            });
        }

        if (Schema::hasTable('customers')) {
            foreach (DB::table('customers')->orderBy('id')->cursor() as $c) {
                if (DB::table('users')->where('id', $c->id)->exists()) {
                    continue;
                }
                DB::table('users')->insert([
                    'id' => $c->id,
                    'name' => trim($c->first_name.' '.$c->last_name),
                    'email' => $c->email,
                    'email_verified_at' => null,
                    'password' => $c->password,
                    'remember_token' => null,
                    'role' => 'client',
                    'first_name' => $c->first_name,
                    'last_name' => $c->last_name,
                    'username' => $c->username,
                    'contact' => $c->contact,
                    'address' => $c->address,
                    'age' => $c->age,
                    'gender' => $c->gender,
                    'status' => (int) $c->status,
                    'created_at' => $c->created_at,
                    'updated_at' => $c->updated_at,
                ]);
            }
        }

        $this->syncUsersAutoIncrement();

        Schema::disableForeignKeyConstraints();

        foreach (['loan_payments', 'loan_records', 'loan_details'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['customer_id']);
                });
            }
        }

        foreach (['loan_details', 'loan_records', 'loan_payments'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreign('customer_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }
        }

        Schema::enableForeignKeyConstraints();

        Schema::dropIfExists('customers');
    }

    public function down(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('last_name', 60);
            $table->string('first_name', 60);
            $table->binary('profile_image')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('email', 60)->unique();
            $table->string('contact', 20)->nullable();
            $table->string('username', 60)->unique();
            $table->string('password');
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('gender', 32)->nullable();
            $table->timestamps();
        });

        foreach (DB::table('users')->where('role', 'client')->orderBy('id')->cursor() as $u) {
            DB::table('customers')->insert([
                'id' => $u->id,
                'first_name' => $u->first_name,
                'last_name' => $u->last_name,
                'profile_image' => null,
                'address' => $u->address,
                'email' => $u->email,
                'contact' => $u->contact,
                'username' => $u->username,
                'password' => $u->password,
                'status' => $u->status,
                'age' => $u->age,
                'gender' => $u->gender,
                'created_at' => $u->created_at,
                'updated_at' => $u->updated_at,
            ]);
        }

        $this->syncCustomersAutoIncrement();

        Schema::disableForeignKeyConstraints();

        $borrowerFk = Schema::hasColumn('loan_details', 'user_id') ? 'user_id' : 'customer_id';

        foreach (['loan_payments', 'loan_records', 'loan_details'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $borrowerFk)) {
                Schema::table($table, function (Blueprint $blueprint) use ($borrowerFk) {
                    $blueprint->dropForeign([$borrowerFk]);
                });
            }
        }

        foreach (['loan_details', 'loan_records', 'loan_payments'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $borrowerFk)) {
                Schema::table($table, function (Blueprint $blueprint) use ($borrowerFk) {
                    $blueprint->foreign($borrowerFk)->references('id')->on('customers')->cascadeOnDelete();
                });
            }
        }

        Schema::enableForeignKeyConstraints();

        DB::table('users')->where('role', 'client')->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn([
                'first_name', 'last_name', 'username', 'contact', 'address', 'age', 'gender', 'status',
            ]);
        });
    }

    private function syncUsersAutoIncrement(): void
    {
        $max = (int) DB::table('users')->max('id');
        if ($max < 1) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users AUTO_INCREMENT = '.($max + 1));
        } elseif ($driver === 'sqlite') {
            $updated = DB::update('UPDATE sqlite_sequence SET seq = ? WHERE name = ?', [$max, 'users']);
            if ($updated === 0) {
                DB::insert('INSERT INTO sqlite_sequence (name, seq) VALUES (?, ?)', ['users', $max]);
            }
        }
    }

    private function syncCustomersAutoIncrement(): void
    {
        $max = (int) DB::table('customers')->max('id');
        if ($max < 1) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE customers AUTO_INCREMENT = '.($max + 1));
        } elseif ($driver === 'sqlite') {
            $updated = DB::update('UPDATE sqlite_sequence SET seq = ? WHERE name = ?', [$max, 'customers']);
            if ($updated === 0) {
                DB::insert('INSERT INTO sqlite_sequence (name, seq) VALUES (?, ?)', ['customers', $max]);
            }
        }
    }
};
