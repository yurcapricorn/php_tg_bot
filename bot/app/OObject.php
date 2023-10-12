<?php

namespace App;


class OObject extends AbstractModel
{
    protected $guarded = [''];
    public $timestamps = false;
    protected $table = 'Objects';

    protected $fillable = [
        'name', 'type', 'image_path', 'description', 'campus', 'stage', 'room'
    ];

    public function getId(){
        return $this->id;
    }

}
