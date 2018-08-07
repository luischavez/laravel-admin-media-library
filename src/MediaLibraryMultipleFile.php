<?php

namespace Luischavez\Admin\Media\library;

use Encore\Admin\Form\Field\MultipleFile;
use Spatie\MediaLibrary\Models\Media;

class MediaLibraryMultipleFile extends MultipleFile
{

    public function objectUrl($media)
    {
        return parent::objectUrl(Media::findOrFail($media['id'])->getPath());
    }
}
