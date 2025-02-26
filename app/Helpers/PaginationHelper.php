<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationHelper
{
    /**
     * Paginate a collection of items.
     *
     * @param \Illuminate\Support\Collection $data
     * @param int $perPage
     * @param int $page
     * @param array $options
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function paginate($data, $perPage = 10, $page = 1, $options = [])
    {
        $perPage = min($perPage, 100);

        return new LengthAwarePaginator(
            $data->forPage($page, $perPage),
            $data->count(), 
            $perPage,
            $page,
            $options 
        );
    }
}