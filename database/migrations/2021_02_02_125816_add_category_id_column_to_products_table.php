<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdColumnToProductsTable extends Migration
{
    const TABLE = 'products';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->foreignIdFor(Category::class)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $columns = ['category_id'];

        if (Schema::hasColumns(self::TABLE, $columns)) {
            Schema::dropColumns(self::TABLE, $columns);
        }
    }
}
