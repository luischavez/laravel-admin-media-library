<?php

namespace Luischavez\Admin\Media\library;

use Illuminate\Support\Facades\URL;
use Encore\Admin\Form\Field\File;
use Encore\Admin\Form\NestedForm;
use Spatie\MediaLibrary\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaLibraryFile extends File
{

    protected $view = 'admin::form.file';

    public function fill($data)
    {
        parent::fill($data);

        if (!empty($this->value)) {
            $this->value = $this->value[0]['id'];
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

    public function prepare($file)
    {
        if (request()->has(static::FILE_DELETE_FLAG)) {
            return $this->destroy();
        }

        return [$this->prepareMedia($file)];
    }

    protected function prepareMedia(UploadedFile $file = null)
    {
        $this->name = $this->getStoreName($file);

        $this->form->model()->clearMediaCollection($this->column());
        $media = $this->form->model()->addMedia($file)->preservingOriginal()->toMediaCollection($this->column())->toArray();
        $media[NestedForm::REMOVE_FLAG_NAME] = 0;

        return tap($media, function () {
            $this->name = null;
        });
    }

    protected function initialPreviewConfig()
    {
        $file = $this->value;

        $config = [];

        $media = Media::where('id', '=', $file)->first();

        $type = 'image';

        switch ($media->mime_type) {
            case 'image/jpeg':
            case 'image/png':
                $type = 'image';
                break;
            case 'application/pdf':
                $type = 'pdf';
                break;
            case 'text/plain':
                $type = 'text';
                break;
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            case 'application/vnd.ms-excel':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'application/vnd.ms-powerpoint':
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                $type = 'office';
                break;
            case 'image/tiff':
                $type = 'gdocs';
                break;
            case 'text/html':
                $type = 'html';
                break;
            case 'video/mp4':
            case 'application/mp4':
            case 'video/x-sgi-movie':
                $type = 'video';
                break;
            case 'audio/mpeg':
            case 'audio/mp3':
                $type = 'audio';
                break;
        }

        $entry = [
            'caption' => $media->file_name,
            'key'     => $media->id,
            'size'    => $media->size
        ];

        if (!empty($type)) {
            $entry['type'] = $type;
        }

        $config[] = $entry;
        
        return $config;
    }

    public function destroy()
    {
        $file = $this->original[0];

        $file[NestedForm::REMOVE_FLAG_NAME] = 1;

        $media = Media::whereId($file['id'])->first();
        $media->delete();

        return [$file];
    }
}
