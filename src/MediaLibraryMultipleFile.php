<?php

namespace Luischavez\Admin\Media\library;

use Encore\Admin\Form\Field\UploadField;
use Encore\Admin\Form\Field\MultipleFile;
use Spatie\MediaLibrary\Models\Media;

class MediaLibraryMultipleFile extends MultipleFile
{

    use UploadField {
        objectUrl as protected traitObjectUrl;
    }

    public function objectUrl($media)
    {
        return $this->traitObjectUrl(Media::findOrFail($media['id'])->getPath());
    }
}
