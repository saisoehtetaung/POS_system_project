<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('login_id', 32)->nullable()->after('role_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('login_id');
            $table->unique('email');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['login_id']);
            $table->dropUnique(['email']);
        });

        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('login_id');
            $table->unique('email');
        });
    }
};
