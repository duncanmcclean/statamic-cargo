<?php

namespace DuncanMcClean\Cargo\Query;

use Statamic\Query\Builder;

class LineItemQueryBuilder extends Builder
{
    public function inRandomOrder()
    {
        //
    }

    protected function getCountForPagination()
    {
        //
    }

    public function count()
    {
        //
    }

    public function get($columns = ['*'])
    {
        //
    }

    public function pluck($column, $key = null)
    {
        //
    }

    public function getWheres()
    {
        return $this->wheres;
    }
}
