<?php

namespace Statamic\Addons\Profiler;

use Statamic\API\Fieldset as FieldsetAPI;
use Statamic\API\Request;
use Statamic\API\User;
use Statamic\CP\Publish\ValidationBuilder;
use Statamic\Extend\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProfilerController extends Controller
{
    use Core;

    /**
     * Update a user with new data.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function postEdit()
    {
        if ($user = User::getCurrent()) {
            $fields = $this->getFields($user->fieldset());
            $validator = $this->runValidation($fields);

            if ($validator->fails()) {
                return back()->withInput()->withErrors($validator, 'profiler');
            }

            // are we resetting a password too?
            if (Request::has('password')) {
                $user->password(Request::get('password'));
                $user->setPasswordResetToken(null);
            }
            // if there's a username, set it (in case it's changing)
            if (Request::has('username')) {
                $user->username(Request::get('username'));
            }

            $user
                ->data(
                    array_merge(
                        $user->data(),
                        array_except($fields, 'username'),
                        $this->uploadFiles($user->fieldset())
                    )
                )->save();

            return Request::has('redirect') ? redirect(Request::get('redirect')) : back();
        } else {
            return back()->withInput()->withErrors('Not logged in', 'profiler');
        }
    }

    /**
     * Get the Validator instance
     *
     * @return mixed
     */
    private function runValidation($fields = [])
    {
        $fieldset = FieldsetAPI::get('user');
        // get all the fields that are files
        $fileFields = collect($fields)->filter(function ($value) {
            return ($value instanceof UploadedFile);
        });

        /* if the fieldset has assets AND there are no assets in the fields, remove the validation
           on the assumption that if there's no file in the request, they don't want to change it

           @todo how to handle file deletions???????
        */
        if ($this->fieldsetHasAssets($fieldset) &&
            $fileFields->count() == 0) {
            $rules = collect((new ValidationBuilder($fields, $fieldset))->build()->rules())
                // set the file ones to null
                ->map(function ($item, $key) use ($fields) {
                    list($ignored, $actualKey) = explode('.', $key);
                    return array_has($fields, $actualKey) ? $item : null;
                })
                // filter out the null ones
                ->filter(function ($value) {
                    return $value;
                })
                ->all();
        }

        // ensure there's a username
        $rules['fields.username'] = 'required';
        $fields['username'] = Request::has('username') ? Request::get('username') : User::getCurrent()->username();

        // if we're resetting the password, add the validation rules and the fields
        if (Request::has('password')) {
            $rules['fields.password'] = 'required|confirmed';

            $fields += Request::only(['password', 'password_confirmation']);
        }

        return app('validator')->make(['fields' => $fields], $rules);
    }

    private function fieldsetHasAssets($fieldset)
    {
        return collect($fieldset->fields())->contains(function ($key, $field) {
            return $field['type'] != 'assets';
        });
    }
}
