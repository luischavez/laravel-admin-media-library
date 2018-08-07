<?php

namespace Luischavez\Admin\Media\library;

use Illuminate\Routing\Controller as BaseController;

use Spatie\MediaLibrary\Models\Media;

class MediaLibraryController extends BaseController
{

    public function download($id)
    {
        $media = Media::findOrFail($id);

        return response()->download($media->getPath(), $media->file_name);
    }
}
