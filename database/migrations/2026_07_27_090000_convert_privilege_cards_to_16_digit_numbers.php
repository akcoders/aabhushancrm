<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('privilege_cards')->orderBy('id')->each(function ($card) {
            DB::table('privilege_cards')
                ->where('id', $card->id)
                ->update(['card_number' => $this->uniqueCardNumber()]);
        });
    }

    public function down(): void
    {
        // Existing card numbers cannot be reconstructed after conversion.
    }

    private function uniqueCardNumber(): string
    {
        do {
            $number = '9'.implode('', array_map(
                fn () => (string) random_int(0, 9),
                range(1, 15)
            ));
        } while (DB::table('privilege_cards')->where('card_number', $number)->exists());

        return $number;
    }
};
