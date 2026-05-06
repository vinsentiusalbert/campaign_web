<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kam_global_saldos')) {
            Schema::create('kam_global_saldos', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Global KAM');
                $table->decimal('balance', 18, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('kam_global_saldo_histories')) {
            Schema::create('kam_global_saldo_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kam_global_saldo_id')->constrained('kam_global_saldos')->cascadeOnDelete();
                $table->decimal('amount', 18, 2);
                $table->decimal('balance_after', 18, 2);
                $table->string('note')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        $globalSaldoId = DB::table('kam_global_saldos')->orderBy('id')->value('id');

        if (! $globalSaldoId) {
            $initialBalance = Schema::hasColumn('users', 'kam_saldo')
                ? (float) DB::table('users')->sum('kam_saldo')
                : 0;

            $globalSaldoId = DB::table('kam_global_saldos')->insertGetId([
                'name' => 'Global KAM',
                'balance' => $initialBalance,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('user_kam_saldo_histories') && ! DB::table('kam_global_saldo_histories')->exists()) {
            $legacyRows = DB::table('user_kam_saldo_histories')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $runningBalance = 0;

            foreach ($legacyRows as $row) {
                $runningBalance += (float) $row->amount;

                DB::table('kam_global_saldo_histories')->insert([
                    'kam_global_saldo_id' => $globalSaldoId,
                    'amount' => $row->amount,
                    'balance_after' => $runningBalance,
                    'note' => $row->note,
                    'created_by' => $row->created_by,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            DB::table('kam_global_saldos')->where('id', $globalSaldoId)->update([
                'balance' => $runningBalance,
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('user_kam_saldo_histories')) {
            Schema::drop('user_kam_saldo_histories');
        }

        if (Schema::hasColumn('users', 'kam_saldo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('kam_saldo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'kam_saldo') === false) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('kam_saldo', 18, 2)->default(0)->after('role');
            });
        }

        if (! Schema::hasTable('user_kam_saldo_histories')) {
            Schema::create('user_kam_saldo_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 18, 2);
                $table->decimal('balance_after', 18, 2);
                $table->string('note')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        Schema::dropIfExists('kam_global_saldo_histories');
        Schema::dropIfExists('kam_global_saldos');
    }
};
