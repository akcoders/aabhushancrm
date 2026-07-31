<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('video_call_sales', fn (Blueprint $table) => $table->string('guest_token', 64)->nullable()->unique()->after('meeting_url'));
        foreach (DB::table('video_call_sales')->orderBy('id')->cursor() as $call) {
            $token = Str::random(48);
            DB::table('video_call_sales')->where('id', $call->id)->update([
                'guest_token' => $token,
                'meeting_url' => rtrim(config('app.url'), '/').'/video-invite/'.$token,
            ]);
        }
    }
    public function down(): void { Schema::table('video_call_sales', fn (Blueprint $table) => $table->dropColumn('guest_token')); }
};
