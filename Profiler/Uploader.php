<?php

namespace Statamic\Addons\Profiler;

use Statamic\API\Helper;
use Statamic\CP\Fieldset;
use Statamic\Forms\Uploaders\AssetUploader;

class Uploader
{
    /** @var Fieldset */
    private $fieldset;

    public function __construct(Fieldset $fieldset)
    {
        $this->fieldset = $fieldset;
    }

    public function upload()
    {
        return collect($this->fieldset->fields())
            ->filter(function ($field) {
                // Only deal with uploadable fields
                return in_array(array_get($field, 'type'), ['assets']);
            })->map(function ($config, $field) {
                // Map into a nicer data schema to work with
                return compact('field', 'config');
            })->reject(function ($arr) {
                // Remove if no file was uploaded
                return !(request()->hasFile($arr['field']));
            })->map(function ($arr, $field) {
                // Add the uploaded files to our data array
                $arr['files'] = collect(array_filter(Helper::ensureArray(request()->file($field))));

                $config = array_get($arr, 'config');
                $uploader = new AssetUploader($config, array_get($arr, 'files'));

                $assets = $uploader->upload();

                // AssetUploader always returns an array so if we only want one,
                // use the first one
                if (array_get($config, 'max_files', 0) == 1) {
                    $assets = $assets[0];
                }

                return $assets;
            })->all();
    }
}
