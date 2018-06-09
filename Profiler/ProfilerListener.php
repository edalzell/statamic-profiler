<?php

namespace Statamic\Addons\Profiler;

use Statamic\API\Fieldset;
use Statamic\Data\Users\User;
use Statamic\Extend\Listener;

class ProfilerListener extends Listener
{
    use Core;

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
        $user
            ->data(array_merge($user->data(), $this->uploadFiles($user->fieldset())))
            ->save();
    }
}
