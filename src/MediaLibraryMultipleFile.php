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

        $medias = Media::whereIn('id', $files)->get();

        foreach ($medias as $media) {
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
