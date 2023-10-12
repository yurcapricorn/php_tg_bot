<?php

namespace App\Http\Controllers;


use App\AbstractModel;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserController extends AbstractController
{
    public static function getById($id){
        return User::where('id', $id)->first();
    }

    public static function getByTgId($tg_id){
        return User::where('tg_id', $tg_id)->first();
    }

    public static function getAll() {
        return User::all();
    }

    public static function create(Array $data){
        $user = new User($data);
        $user->save();
        return $user;
    }

    public static function delete(AbstractModel $user){
        $user->delete();
    }

    public static function seed(){
        $roles = ['adm', 'employee', 'student', 'trainee'];
        $campuses = ['MSK', 'NSK', 'KZN'];
        for($i=0;$i<10;$i++){
            static::create([
                'name' => "name_$i",
                'role' => $roles[$i%4],
                'campus' => $campuses[$i%3],
                'tg_id' => $i + 123456789,
                'login' => $i . $roles[$i%4] . $campuses[$i%3],
            ]);
        }
    }

    private static function up() {
        Schema::create('Users', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->primary(['id']);
            $table->string('name')->nullable();
            $table->string('role')->nullable();
            $table->string('login')->nullable();
            $table->string('campus')->nullable();
            $table->bigInteger('tg_id');
            $table->string('tg_current_phase')->nullable();
            $table->string('tg_state')->nullable();
        });
    }

    private static function down(){
        Schema::dropIfExists('Users');
    }

    public static function reset() {
        static::down();
        static::up();
    }
}