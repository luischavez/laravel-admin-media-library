<?php

namespace Luischavez\Admin\Media\library;

use Illuminate\Support\Facades\URL;
use Encore\Admin\Form\Field\MultipleFile;
use Encore\Admin\Form\NestedForm;
use Spatie\MediaLibrary\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaLibraryMultipleFile extends MultipleFile
{
    protected $view = 'admin::form.multiplefile';

    public function fill($data)
    {
        parent::fill($data);

        foreach ($this->value as $key => $media) {
            $this->value[$key] = $media['id'];
        }
    }

    public function objectUrl($mediaId)
    {
        return URL::route('admin.media.download', $mediaId);
    }

    public function original()
    {
        $original = parent::original();

        foreach ($original as $key => $file) {
            $original[$key][NestedForm::REMOVE_FLAG_NAME] = 0;
        }

        return $original;
    }

    public function prepare($files)
    {
        if (request()->has(static::FILE_DELETE_FLAG)) {
            return $this->destroy(request(static::FILE_DELETE_FLAG));
        }

        $targets = array_map([$this, 'prepareMedia'], $files);

        return array_merge($this->original(), $targets);
    }

    protected function prepareMedia(UploadedFile $file = null)
    {
        $this->name = $this->getStoreName($file);

        $media = $this->form->model()->addMedia($file)->preservingOriginal()->toMediaCollection($this->column())->toArray();
        $media[NestedForm::REMOVE_FLAG_NAME] = 0;

        return tap($media, function () {
            $this->name = null;
        });
    }

    protected function initialPreviewConfig()
    {
        $files = $this->value ?: [];

        $config = [];

        foreach ($files as $index => $file) {
            $config[] = [
                'caption' => basename($file),
                'key'     => $file,
            ];
        }

        return $config;
    }

    public function destroy($key)
    {
        $files = $this->original ?: [];

        foreach ($files as $fileKey => $file) {
            $files[$fileKey][NestedForm::REMOVE_FLAG_NAME] = $file['id'] == $key ? 1 : 0;

            if ($file['id'] == $key) {
                $media = Media::whereId($key)->first();
                $media->delete();
            }
        }

        return array_values($files);
    }
}
