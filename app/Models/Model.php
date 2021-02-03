<?php

namespace App\Models;

use Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Model extends EloquentModel
{
    /**
     * The attributes that may be used by scopeFilter when filtering search.
     *
     * @var array
     */
    protected $filterable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->filterable = $this->filterable ?? $this->getFillable();
        $this->filterable = array_combine($this->filterable, $this->filterable);
    }

    /**
     * Returns an attribute value without triggering any accessor.
     *
     * @param string $key
     * @return mixed
     */
    public function getActualAttribute(string $key)
    {
        return Arr::get($this->attributes, $key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isFilterable(string $key): bool
    {
        return (bool)($this->filterable[$key] ?? false);
    }

    /**
     * @param Builder $query
     * @param array $filter_fields
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filter_fields): Builder
    {
        foreach ($filter_fields as $field_name => $search_value) {
            if (empty($search_value) || !$this->isFilterable($field_name)) {
                continue;
            }

            if (is_string($search_value)) {
                $search_value = preg_replace('/(?<!\\\)\\\%/', '\%', $search_value);
                $search_value_prepared_to_like = preg_replace('/(?<!\\\)\*+/', '%', $search_value);
            }

            if (isset($search_value_prepared_to_like) && $search_value !== $search_value_prepared_to_like) {
                $query->where($field_name, 'like', $search_value_prepared_to_like);
                continue;
            }

            $query->where($field_name, $search_value);
        }

        return $query;
    }
}
