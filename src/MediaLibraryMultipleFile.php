<?php

namespace Luischavez\Admin\Media\library;

use Encore\Admin\Form\Field\MultipleFile;
use Spatie\MediaLibrary\Models\Media;

class MediaLibraryMultipleFile extends MultipleFile
{
    protected $view = 'admin::form.multiplefile';

    public function objectUrl($media)
    {
        return URL::route('admin.media.download', $media['id']);
    }
}
