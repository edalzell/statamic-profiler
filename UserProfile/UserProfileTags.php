<?php

namespace Statamic\Addons\UserProfile;

use Statamic\API\User;
use Statamic\API\Request;
use Statamic\Extend\Tags;

class UserProfileTags extends Tags {
    /**
     * The {{ user_profile:edit_form }} tag
     *
     * @return string|array
     */
    public function editForm() {
        $data = [];
        if ($user = User::getCurrent()) {
            $data = $user->data();
            $data['username'] = $user->username();
            $data['email'] = $user->email();

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
    private function getRedirectUrl() {
        $return = $this->get('redirect');

        if ($this->getBool('allow_request_redirect')) {
            $return = Request::input('redirect', $return);
        }

        return $return;
    }

    /**
     * Maps to {{ user_profile:success }}
     *
     * @return bool
     */
    public function success() {
        return $this->flash->exists('success');
    }

    /**
     * Maps to {{ user_profile:errors }}
     *
     * @return bool|string
     */
    public function errors() {
        if (!$this->hasErrors()) {
            return false;
        }

        $errors = [];

        foreach (session('errors')->getBag('user_profile')->all() as $error) {
            $errors[]['value'] = $error;
        }

        return ($this->content === '')    // If this is a single tag...
            ? !empty($errors)             // just output a boolean.
            : $this->parseLoop($errors);  // Otherwise, parse the content loop.
    }

    /**
     * Does this form have errors?
     *
     * @return bool
     */
    private function hasErrors() {
        return (session()->has('errors'))
            ? session('errors')->hasBag('user_profile')
            : false;
    }

    /**
     * Get the errorBag from session
     *
     * @return object
     */
    private function getErrorBag() {
        if ($this->hasErrors()) {
            return session('errors')->getBag('user_profile');
        }
    }
}
