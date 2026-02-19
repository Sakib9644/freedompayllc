<?php
namespace Modules\Gallery\Helpers;

use Modules\Gallery\Models\File;

class Resource
{
    public static function getFile($ids){
        $data = null;
        if($ids != null){
            if(is_array($ids)){
                $data = File::whereIn('id', $ids)->get()->pluck('path');
            }elseif(is_numeric($ids)){
                $file = File::where('id', $ids)->first();
                $data = $file->path;
            }
        }
        return $data;
    }
}
