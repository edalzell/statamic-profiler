<?php

namespace Statamic\Addons\UserProfile;

use Statamic\API\User;
use Statamic\API\Helper;
use Statamic\API\Request;
use Statamic\CP\Fieldset;
use Statamic\Extend\Controller;
use Statamic\CP\Publish\ValidationBuilder;

class UserProfileController extends Controller {

    /** @var Fieldset $fieldset */
    private $fieldset;

    private $fields;

    /** @var \Statamic\Data\Users\User $user */
    private $user;

    public function __construct() {
        if ($this->user = User::getCurrent()) {

            $this->fieldset = $this->user->fieldset();

        }
    }

    /**
     * Update a user with new data.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function postEdit() {
        if ($user = User::getCurrent()) {


            $this->fields = array_intersect_key(Request::all(),
                                                array_flip(array_keys(array_merge($this->fieldset->fields(),
                                                                                  $this->fieldset->taxonomies()))));

            $validator = $this->runValidation();

            if ($validator->fails()) {
                return back()->withInput()->withErrors($validator);
            }

            $this->uploadFiles();

            // there will always be a username here because otherwise the validation would have failed.
            $user->username(Request::get('username'));

            if (Request::has('email')) {
                $user->email(Request::get('email'));
            }

            // are we resetting a password too?
            //$user->password($password);

            $user->setPasswordResetToken(null);


            $user->data(array_merge($user->data(), $this->fields));

            $user->save();

            return Request::has('redirect') ? redirect(Request::get('redirect')) : back();

        } else {
            return back()->withInput()->withErrors('Not logged in', 'user_profile');
        }
    }

    public function uploadFiles()
    {
        $request = request();

        $asset_ids = collect($this->fieldset->fields())->filter(function ($field) {
            // Only deal with uploadable fields
            return in_array(array_get($field, 'type'), ['file', 'files', 'asset', 'assets']);

        })->map(function ($config, $field) {
            // Map into a nicer data schema to work with
            return compact('field', 'config');

        })->reject(function ($arr) use ($request) {
            // Remove if no file was uploaded
            return !$request->hasFile($arr['field']);

        })->map(function ($arr, $field) use ($request) {
            // Add the uploaded files to our data array
            $files = collect(array_filter(Helper::ensureArray($request->file($field))));
            $arr['files'] = $files;

            // A plural type uses the singular version. assets => asset, etc.
            $type = rtrim(array_get($arr, 'config.type'), 's');

            // Upload the files
            $class = 'Statamic\Forms\Uploaders\\'.ucfirst($type).'Uploader';
            $config = array_get($arr, 'config');
            $uploader = new $class($config, array_get($arr, 'files'));

            $assets = $uploader->upload();

            // @todo remove when https://github.com/statamic/v2-hub/issues/1767 is resolved
            if (!$this->multipleFilesAllowed($config)) {
                $assets = $assets[0];
            }

            // end todo

            return $assets;
        })->all();

        $this->fields = array_merge($this->fields, $asset_ids);
    }


    /**
     * Get the Validator instance
     *
     * @return mixed
     */
    private function runValidation() {

        $additional_validations = ['username' => 'required'];
        // if we're resetting the password, add the validation
        if (Request::has('password')) {
            $additional_validations['password'] = 'required|confirmed';
        }

        $fields = array_merge($this->fields, $additional_validations);

        $builder = new ValidationBuilder(['fields' => $fields], $this->fieldset);

        $builder->build();

        return app('validator')->make(['fields' => $fields], $builder->rules());
    }

    /**
     * Are multiple files allowed to be uploaded?
     *
     * @param $config array
     *
     * @return bool
     */
    private function multipleFilesAllowed($config)
    {
        return array_get($config, 'type') === 'assets' &&  array_get($config, 'max_files', 0) != 1;
    }

}
