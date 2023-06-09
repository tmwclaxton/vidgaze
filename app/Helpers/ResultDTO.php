<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;
use Carbon\Carbon;

class ResultDTO
{
    public CreatorDTO $creator;
    public ContentDTO $content;
    public Platform | string $platform;
    public Kind $kind;


    public function __construct(Platform $platform, Kind $kind)
    {
        $this->platform = $platform;
        $this->kind = $kind;
    }


    public function save(){
        $creator = null;
        if(isset($this->creator)){
            $creator = $this->creator->save();
            if($this->kind == Kind::Creator){
                return $creator;
            }
        }
        if(isset($this->content)){
            return $this->content->save($creator);
        }
    }




}
