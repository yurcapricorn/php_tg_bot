<?php

namespace App\Http\Controllers;


use App\AbstractModel;
use App\OObject;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ObjectController extends AbstractController
{
    public static function getById($id){
        return OObject::where('id', $id)->first();
    }

    public static function getAll() {
        return OObject::all()->sortBy('type');
    }

    public static function getByCampusAndType($campus, $type){
        return OObject::where('campus', $campus)->where('type', $type)->get()->toArray();
    }

    public static function getByCampus($campus){
        return OObject::where('campus', $campus)->get();
    }

    public static function create(Array $data){
        $object = new OObject($data);
        $object->save();
        return $object;
    }

    public static function delete(AbstractModel $object){
        $object->delete();
    }

    public static function seed(){
        $types = ['meeting_room', 'kitchen', 'table_games', 'sport_games'];
        $campuses = ['MSK', 'NSK', 'KZN'];
        for($i=0;$i<20;$i++){
            static::create([
                'name' => $types[$i%4] . "_$i",
                'type' => $types[$i%4],
                'campus' => $campuses[$i%3],
                'description' => "description_$i",
                'stage' => rand(-1,3),
                'room' => rand(0,399),
            ]);
        }
    }

    private static function up() {
        Schema::create('Objects', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->primary(['id']);
            $table->string('name');
            $table->string('type');
            $table->string('description');
            $table->string('campus');
            $table->string('image_path')->nullable();
            $table->integer('stage');
            $table->integer('room');
        });
    }

    private static function down(){
        Schema::dropIfExists('Objects');
    }

    public static function reset() {
        static::down();
        static::up();
    }
}