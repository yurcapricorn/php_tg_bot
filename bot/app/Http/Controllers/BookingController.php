<?php


namespace App\Http\Controllers;


use App\AbstractModel;
use App\Booking;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookingController extends AbstractController
{
    public static function getById($id){
        return Booking::where('id', $id)->first();
    }

    public static function getAll() {
        return Booking::all();
    }

    public static function getFreeTime($object_id, $timestamp){
        $date = date('Y-m-d', $timestamp);
        $from = strtotime($date);
        $to = $from + 86400;
        $bookings = Booking::where('object_id', $object_id)->whereBetween('start_time', [$from, $to])->get()->toArray();
//        echo '<pre>';
//        print_r($bookings);
        $busy = [];
        foreach ($bookings as $booking){
            $busy[intval(date('H', $booking['start_time']))] = 'busy';
        }
//        print_r($busy);
        $free = [];
        for($i=0;$i<=24;$i++){
            if(empty($busy[$i])){
                $free[$i] = 'free';
            }
        }
        return $free;
    }

    public static function getByUserId($id){
        return Booking::where('user_id', $id)->get();
    }

    public static function getByObjectId($id){
        return Booking::where('object_id', $id)->get();
    }

    public static function delete(AbstractModel $booking){
        $booking->delete();
    }

    public static function create(Array $data){
        $booking = new Booking($data);
        $booking->save();
        return $booking;
    }

    public static function seed(){
        $objects = ObjectController::getAll()->toArray();
        $objects_ids = array_column($objects, 'id');
        $users = UserController::getAll()->toArray();
        $users_ids = array_column($users, 'id');
        foreach($objects_ids as $object_id){
            foreach ($users_ids as $user_id){
                static::create([
                    'user_id' => $user_id,
                    'object_id' => $object_id,
                    'start_time' => strtotime('01.09.2022 12:00'),
                    'end_time' => strtotime('01.09.2022 15:00'),
                    'status' => 'active'
                ]);
            }

        }
    }

    private static function up() {
        Schema::create('Bookings', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->primary(['id']);
            $table->bigInteger('user_id');
            $table->bigInteger('object_id');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->string('status');
        });
    }

    private static function down(){
        Schema::dropIfExists('Bookings');
    }

    public static function reset() {
        static::down();
        static::up();
    }

}