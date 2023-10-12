<?php

namespace App;


class User extends AbstractModel
{
    protected $guarded = [''];
    public $timestamps = false;
    protected $table = 'Users';

    protected $fillable = [
        'name', 'role', 'login', 'tg_id', 'campus', 'tg_state', 'tg_current_phase'
    ];

    public function getState(){
        return json_decode($this->tg_state,true);
    }

    public function updateState($key, $value){
        $state = $this->getState();
        $state[$key] = $value;
        $this->tg_state = json_encode($state);
        $this->save();
    }

    public function clearState(){
        $this->tg_state = '';
        $this->tg_current_phase = '';
        $this->save();
    }
}
