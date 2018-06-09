<?php

namespace Statamic\Addons\Profiler;

use Statamic\API\Request;
use Statamic\API\URL;
use Statamic\API\User;

class ProfilerTags extends Tags
{
    /**
     * The {{ profiler:edit_form }} tag
     *
     * @return string|array
     */
    public function editForm()
    {
        $data = [];
        if ($user = User::getCurrent()) {
            $data = $user->data();
            $data['username'] = $user->username();

            if ($this->success()) {
                $data['success'] = true;
            }

            if ($this->hasErrors()) {
                $data['errors'] = $this->getErrorBag()->all();
            }

            $html = $this->formOpen('edit');

            if ($redirect = $this->getRedirectUrl()) {
                $html .= '<input type="hidden" name="redirect" value="' . $redirect . '" />';
            }

            return $html . $this->parse($data) . '</form>';
        } else {
            $data['errors'] = ['Must be logged in'];

            return $this->parse($data);
        }
    }

    /**
     * Get the redirect URL
     *
     * @return string
     */
    private function getRedirectUrl()
    {
        return $this->getBool('allow_request_redirect', false)
        ? Request::input('redirect')
        : $this->get('redirect');
    }

    /**
     * Maps to {{ user_profile:success }}
     *
     * @return bool
     */
    public function success()
    {
        return $this->flash->exists('success');
    }

    /**
     * Maps to {{ user_profile:errors }}
     *
     * @return bool|string
     */
    public function errors()
    {
        if (!$this->hasErrors()) {
            return false;
        }

        $errors = [];

        foreach ($this->getErrorBag()->all() as $error) {
            $errors[]['value'] = $error;
        }

        return ($this->content === '') // If this is a single tag...
         ? !empty($errors) // just output a boolean.
         : $this->parseLoop($errors); // Otherwise, parse the content loop.
    }

    /**
     * Does this form have errors?
     *
     * @return bool
     */
    private function hasErrors()
    {
        return (session()->has('errors'))
        ? session('errors')->hasBag('profiler')
        : false;
    }

    /**
     * Get the errorBag from session
     *
     * @return object
     */
    private function getErrorBag()
    {
        if ($this->hasErrors()) {
            return session('errors')->getBag('profiler');
        }
    }
}
