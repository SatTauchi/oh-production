<?php

namespace App\Policies;

use App\Models\FishPrice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FishPricePolicy
{
    use HandlesAuthorization;

    public function update(User $user, FishPrice $fishPrice)
    {
        return $user->id === $fishPrice->user_id;
    }

    public function delete(User $user, FishPrice $fishPrice)
    {
        return $user->id === $fishPrice->user_id;
    }
}