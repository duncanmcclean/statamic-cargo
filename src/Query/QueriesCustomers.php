<?php

namespace DuncanMcClean\Cargo\Query;

trait QueriesCustomers
{
    public function whereGuestCustomer(): self
    {
        $this->where('customer', 'like', 'guest::%');

        return $this;
    }

    public function whereNotGuestCustomer(): self
    {
        $this->whereNot('customer', 'like', 'guest::%');

        return $this;
    }
}