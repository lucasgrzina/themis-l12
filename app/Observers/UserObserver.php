<?php

namespace App\Observers;

use App\User;

class UserObserver
{
    /*public function created(User $user)
    {
        //
    }*/

    public function deleted(User $model)
    {
        $model->username = $model->id . '_' . $model->username;
        $model->email = $model->id . '_' . $model->email;
        $model->save();
    }
}