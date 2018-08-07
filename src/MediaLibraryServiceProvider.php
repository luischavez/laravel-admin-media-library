<?php

namespace Luischavez\Admin\Media\library;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class MediaLibraryServiceProvider extends ServiceProvider
{

    public function boot()
    {
        Route::model('media', \Spatie\MediaLibrary\Models\Media::class);

        Route::group(['prefix' => 'media'], function ($router) {
            $router->group(['middleware' => config('admin.media.middleware')], function ($router) {
                $router->get('download/{media}', 
                    '\Luischavez\Admin\Media\library\MediaLibraryController@download')
                ->name('admin.media.download');
            });
        });
    }

    public function register()
    {
    }
}
