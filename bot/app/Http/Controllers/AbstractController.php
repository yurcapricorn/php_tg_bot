<?php


namespace App\Http\Controllers;


use App\AbstractModel;
use Illuminate\Routing\Controller;

abstract class AbstractController extends Controller {
    abstract public static function getById($id);
    abstract public static function delete(AbstractModel $model);
    abstract public static function create(Array $arr);

}