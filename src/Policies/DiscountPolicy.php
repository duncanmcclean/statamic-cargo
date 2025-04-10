<?php

namespace DuncanMcClean\Cargo\Policies;

class DiscountPolicy
{
    public function index($user): bool
    {
        return $this->view($user);
    }

    public function view($user): bool
    {
        return $user->can('view discounts');
    }

    public function create($user): bool
    {
        return $user->can('create discounts');
    }

    public function store($user): bool
    {
        return $user->can('create discounts');
    }

    public function edit($user, $discount): bool
    {
        return $user->can('view discounts');
    }

    public function update($user, $discount): bool
    {
        return $user->can('edit discounts');
    }

    public function delete($user, $discount): bool
    {
        return $user->can('delete discounts');
    }
}
