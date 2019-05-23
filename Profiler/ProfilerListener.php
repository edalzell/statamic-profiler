<?php

namespace Statamic\Addons\Profiler;

use Statamic\Data\Users\User;
use Statamic\Extend\Listener;

class ProfilerListener extends Listener
{
    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */
    public $events = [
        'user.registered' => 'upload',
    ];

    public function upload(User $user)
    {
        $uploader = new Uploader($user->fieldset());

        $user
            ->data(array_merge($user->data(), $uploader->upload()))
            ->save();
    }
}
