<?php

namespace Otwell\IconsViewer\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;
use Symfony\Component\Finder\Finder;

class ViewerController extends Controller
{
    public function __invoke(NovaRequest $request)
    {
        return inertia('IconsViewer', [
            'icons' => ray()->pass([
                'solid' => $this->iconSet('solid'),
                'outline' => $this->iconSet('outline'),
            ]),
        ]);
    }

    /**
     * Register all of the resource classes in the given directory.
     *
     * @param  string  $set
     * @return array
     */
    public static function iconSet($set)
    {
        /** @phpstan-ignore-next-line */
        $directory = NOVA_PATH.'/resources/js/components/Heroicons/'.$set;

        return LazyCollection::make(function () use ($directory) {
            yield from (new Finder())->in($directory)->files();
        })
        ->collect()
        ->transform(function ($file) use ($directory, $set) {
            return str_replace(
                "heroicons-",
                '',
                Str::snake(str_replace(
                    ['/', '.vue'],
                    ['', ''],
                    Str::after($file, $directory)
                ), '-'),
            );
        })->reject(function ($file) {
            return $file === 'index.js';
        })->sort()->values()->all();
    }
}
