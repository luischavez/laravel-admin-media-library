<?php

namespace Luischavez\Admin\Media\library;

use Illuminate\Routing\Controller as BaseController;

use Spatie\MediaLibrary\Models\Media;

class MediaLibraryController extends BaseController
{

    public function download(Media $media)
    {
        return response()->download($media->getPath(), $media->file_name);
    }
}
