<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property string $description
 * @property mixed $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $category_id
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Query\Builder|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Product withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'category_id' => 'integer',
        'sku' => 'string',
        'price' => 'decimal:2',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'price',
    ];

    /**
     * The attributes that may be used by scopeFilter when filtering search.
     *
     * @var array
     */
    protected $filterable = [
        'sku',
        'name',
        'description',
        'price',
    ];

    public function scopeFilter(Builder $query, array $filter_fields): Builder
    {
        $category_field_name = 'category';
        $category_name_column_reference = 'categories.name';

        if ($filter_fields[$category_field_name] ?? null) {
            $search_value = preg_replace('/(?<!\\\)\\\%/', '\%', $filter_fields[$category_field_name]);
            $search_value_prepared_to_like = preg_replace('/(?<!\\\)\*+/', '%', $search_value);

            $query->select('products.*')
                ->join('categories', 'products.category_id', '=', 'categories.id');

            if ($search_value !== $search_value_prepared_to_like) {
                $query->where($category_name_column_reference, 'like', $search_value_prepared_to_like);
                return parent::scopeFilter($query, $filter_fields);
            }

            $query->where($category_name_column_reference, $search_value);
        }

        return parent::scopeFilter($query, $filter_fields);
    }

    public function getPriceAttribute($price_value)
    {
        return 'R$ ' . str_replace('.', ',', $price_value);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
