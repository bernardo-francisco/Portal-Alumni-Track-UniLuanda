<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('egressos', function (Blueprint $table) {
            // Verificar se a coluna não existe antes de adicionar
            if (!Schema::hasColumn('egressos', 'empregado')) {
                $table->boolean('empregado')->default(false)->after('verificado');
            }
            
            if (!Schema::hasColumn('egressos', 'area_atuacao')) {
                $table->string('area_atuacao', 100)->nullable()->after('empregado');
            }
            
            if (!Schema::hasColumn('egressos', 'salario_medio')) {
                $table->decimal('salario_medio', 10, 2)->nullable()->after('area_atuacao');
            }
            
            if (!Schema::hasColumn('egressos', 'tempo_insercao')) {
                $table->integer('tempo_insercao')->nullable()->comment('Meses após formatura')->after('salario_medio');
            }
            
            if (!Schema::hasColumn('egressos', 'satisfacao_emprego')) {
                $table->tinyInteger('satisfacao_emprego')->nullable()->comment('1-5')->after('tempo_insercao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('egressos', function (Blueprint $table) {
            $columns = ['empregado', 'area_atuacao', 'salario_medio', 'tempo_insercao', 'satisfacao_emprego'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('egressos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};