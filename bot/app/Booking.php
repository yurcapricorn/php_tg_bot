<?php

namespace App;


class Booking extends AbstractModel
{
    protected $guarded = [''];
    public $timestamps = false;
    protected $table = 'Bookings';

    protected $fillable = [
        'user_id', 'object_id', 'start_time', 'end_time', 'status'
    ];

    public function getId(){
        return $this->id;
    }

}
