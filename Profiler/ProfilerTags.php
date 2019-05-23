<?php

namespace Statamic\Addons\Profiler;

use Statamic\API\User;
use Statamic\API\Request;
use Statamic\Extend\Tags;

class ProfilerTags extends Tags
{
    /**
     * The {{ profiler:edit_form }} tag
     *
     * @return string|array
     */
    public function editForm()
    {
        if (!$user = $this->getUser()) {
            $data['errors'] = ['User not found'];

            return $this->parse($data);
        }

        $data = $user->data();
        $data['username'] = $user->username();

        if ($this->success()) {
            $data['success'] = true;
        }

        if ($this->hasErrors()) {
            $data['errors'] = $this->getErrorBag()->all();
        }

        $html = $this->formOpen("user/{$user->id()}");

        if ($redirect = $this->getRedirectUrl()) {
            $html .= '<input type="hidden" name="redirect" value="' . $redirect . '" />';
        }

        return $html . $this->parse($data) . '</form>';
    }

    /**
     * The {{ profiler:delete_form }} tag
     *
     * @return string|array
     */
    public function deleteForm()
    {
        if (!$user = $this->getUser()) {
            $data['errors'] = ['User not found'];

            return $this->parse($data);
        }

        $data = $user->data();
        $data['username'] = $user->username();

        if ($this->success()) {
            $data['success'] = true;
        }

        if ($this->hasErrors()) {
            $data['errors'] = $this->getErrorBag()->all();
        }

        $html = $this->formOpen("user/{$user->id()}");
        $html .= '<input type="hidden" name="_method" value="DELETE"/>';

        if ($redirect = $this->getRedirectUrl()) {
            $html .= '<input type="hidden" name="redirect" value="' . $redirect . '" />';
        }

        return $html . $this->parse($data) . '</form>';
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
     * Maps to {{ profiler:success }}
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

    private function getUser()
    {
        if ($id = $this->getParam('id')) {
            return User::find($id);
        } elseif ($username = $this->getParam('username')) {
            return User::whereUsername($username);
        } else {
            return User::getCurrent();
        }
    }
}
