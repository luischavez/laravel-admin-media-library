<?php

namespace Luischavez\Admin\Media\library;

use Illuminate\Support\Facades\URL;
use Encore\Admin\Form\Field\MultipleFile;
use Spatie\MediaLibrary\Models\Media;

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
}
