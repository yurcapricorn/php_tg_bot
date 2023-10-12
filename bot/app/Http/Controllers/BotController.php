<?php

namespace App\Http\Controllers;

use App\Logger;

class BotController extends \Illuminate\Routing\Controller
{
    public static function run() {
        date_default_timezone_set('Europe/Moscow');
        $token = file_get_contents(__DIR__ . '/../../../tg_token2.txt');

        try {
            $bot = new \TelegramBot\Api\Client($token);

            $bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot) {
                $message = $update->getMessage();
                $chat_id = $message->getChat()->getId();
                $text = $message->getText();

                $d = file_get_contents("php://input");
                $d = json_decode($d, true);
                $user_tg_id = $d['message']['from']['id'];
                $user = UserController::getByTgId($user_tg_id);

                if (empty($user)) {
                    $user = UserController::create(['tg_id' => $user_tg_id]);
                    $bot->sendMessage($chat_id, "введите имя");
                    $user->tg_current_phase = 'новый пользователь имя';
                    $user->save();
                } elseif (empty($user->login) && $user->tg_current_phase == 'новый пользователь имя') {
                    $user->updateState('name', $text);
                    $bot->sendMessage($chat_id, "введите логин");
                    $user->tg_current_phase = 'новый пользователь логин';
                    $user->save();
                } elseif ($user->tg_current_phase == 'новый пользователь логин') {
                    $user->updateState('login', $text);
                    $kb = [['adm', 'employee', 'student', 'trainee']];
                    $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($kb, true);
                    $bot->sendMessage($chat_id, "выберите роль", null, false, null, $keyboard);
                    $user->tg_current_phase = 'новый пользователь роль';
                    $user->save();
                } elseif ($user->tg_current_phase == 'новый пользователь роль') {
                    $user->updateState('role', $text);
                    $kb = [['MSK', 'NSK', 'KZN']];
                    $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($kb, true);
                    $bot->sendMessage($chat_id, "выберите кампус", null, false, null, $keyboard);
                    $user->tg_current_phase = 'новый пользователь кампус';
                    $user->save();
                } elseif ($user->tg_current_phase == 'новый пользователь кампус') {
                    $campus = $text;
                    $user->name = $user->getState()['name'];
                    $user->login = $user->getState()['login'];
                    $user->role = $user->getState()['role'];
                    $user->campus = $campus;
                    $user->save();
                    $user->clearState();
                    $bot->sendMessage($chat_id, "вы зарегистрировались успешно");
                }

                if (empty($user->campus)) {
                    return;
                }

                if ($text == 'clear') {
                    $user->clearState();
                }

                if ($text == 'drop user') {
                    $user->delete();
                }

                $campus = $user->getAttribute('campus');

                switch ($user->getAttribute('tg_current_phase')) {
                    case '':
                        if ($text == 'брони') {
                            $bookings = BookingController::getByUserId($user->getAttribute('id'));
                            if (empty($bookings)) {
                                $bot->sendMessage($chat_id, "у вас нет броней");
                                $user->clearState();
                                return;
                            }
                            $user->tg_current_phase = 'брони';
                            $user->save();
                            $kb = [];
                            foreach ($bookings as $booking) {
                                $booking = $booking->toArray();
                                $object = ObjectController::getById($booking['object_id']);
                                if (!empty($object)) {
                                    $object = $object->toArray();
                                }
                                $kb[][] = $booking['id'] . ' ' . $object['name'] . ' ' . date('Y-m-d H:i', $booking['start_time']);
                            }
                            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($kb, true);
                            $bot->sendMessage($chat_id, "выберите бронь", null, false, null, $keyboard);
                        } elseif ($text == 'объекты') {
                            $user->tg_current_phase = 'объекты';
                            $user->save();
                            $kb = [];
                            $types = ['meeting_room', 'kitchen', 'table_games', 'sport_games'];
                            foreach ($types as $t) {
                                $kb[][] = $t;
                            }
                            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($kb, true);
                            $bot->sendMessage($chat_id, "выберите тип обьекта", null, false, null, $keyboard);
                        } else {
                            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([['объекты', 'брони']], true);
                            $bot->sendMessage($chat_id, "выберите меню", null, false, null, $keyboard);
                        }
                        break;
                    case 'брони':
                        $booking_id = explode(' ', $text)[0];
                        $booking = BookingController::getById($booking_id);
                        $booking->delete();
                        $user->clearState();
                        $bot->sendMessage($chat_id, "бронь удалена");
                        break;
                    case 'объекты':
                        $user->tg_current_phase = 'тип объекта';
                        $user->save();
                        $objects = ObjectController::getByCampusAndType($campus, $text);
                        $kb = [];
                        foreach ($objects as $t) {
                            $kb[][] = $t['id'] . ' ' . $t['name'];
                        }
                        $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($kb, true);
                        $bot->sendMessage($chat_id, "выберите объект", null, false, null, $keyboard);
                        break;
                    case 'тип объекта':

                        if ($text == 'бронировать') {
                            $object_id = $user->getState()['object_id'];
                            $object = ObjectController::getById($object_id);
                            $user->tg_current_phase = 'день бронирования';
                            $user->save();

                            $user_role = $user->getAttribute('role');
                            switch ($object['type']) {
                                case 'meeting_room':
                                    break;
                                case 'kitchen':
                                    if ($user_role != 'employee') {
                                        $user->clearState();
                                        $bot->sendMessage($chat_id, "бронирование данного объекта запрещено для вашей категории");
                                        return;
                                    }
                                    break;
                                case 'table_games':
                                    if ($user_role == 'adm' || $user_role == 'trainee') {
                                        $user->clearState();
                                        $bot->sendMessage($chat_id, "бронирование данного объекта запрещено для вашей категории");
                                        return;
                                    }
                                    break;
                                case 'sport_games':
                                    if ($user_role == 'adm' || $user_role == 'trainee') {
                                        $user->clearState();
                                        $bot->sendMessage($chat_id, "бронирование данного объекта запрещено для вашей категории");
                                        return;
                                    }
                                    break;
                            }

                            $t = '';
                            foreach ($object->toArray() as $k => $v) {
                                if ($k == 'id') {
                                    continue;
                                }
                                $t .= "$k : $v" . PHP_EOL;
                            }
//                        $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([[$t]], true);
                            $bot->sendMessage($chat_id, $t);
                            $kb = [];
                            for ($i = 0; $i < 20; $i++) {
                                $kb[][] = date('d M Y', strtotime("+$i day", time()));
                            }
                            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($kb, true);
                            $bot->sendMessage($chat_id, "выберите день", null, false, null, $keyboard);
                        } elseif ($text == 'просмотр броней') {
                            $object_id = $user->getState()['object_id'];
                            $user->clearState();
                            $bookings = BookingController::getByObjectId($object_id);
                            if (!empty($bookings)) {
                                $bookings = $bookings->toArray();
                            }
                            $kb = [];
                            foreach ($bookings as $booking) {
                                $object = ObjectController::getById($booking['object_id']);
                                $object_name = $object['name'];
                                $object_type = $object['type'];
                                $start_time = date('Y-m-d H', $booking['start_time']);
                                $kb[][] = $object_name . ' ' . $object_type . ' ' . date("H:i", strtotime($start_time . ' hours'));
                            }
                            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($kb, true);
                            $bot->sendMessage($chat_id, "список броней", null, false, null, $keyboard);
                        } else {
                            $object_id = (explode(' ', $text))[0];
                            $user->updateState('object_id', $object_id);
                            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([['бронировать',
                                'просмотр броней']], true);
                            $bot->sendMessage($chat_id, "выберите меню", null, false, null, $keyboard);
                        }
                        break;
                    case 'день бронирования':
                        $user->tg_current_phase = 'время бронирования';
                        $user->save();
                        $date = strtotime($text);
                        $user->updateState('booking_date', $date);
                        $object_id = $user->getState()['object_id'];
                        $free_slots = BookingController::getFreeTime($object_id, $date);
                        $kb = [];
                        foreach ($free_slots as $k => $v) {
                            $kb[][] = strval($k);
                        }
                        $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($kb, true);
                        $bot->sendMessage($chat_id, "выберите время", null, false, null, $keyboard);
                        break;
                    case 'время бронирования':
                        $booking_time = $text;
                        $from = $user->getState()['booking_date'] + (intval($booking_time)) * 3600;
                        $to = $from + 3600;
                        $data = [
                            'user_id' => $user->getAttribute('id'),
                            'object_id' => $user->getState()['object_id'],
                            'start_time' => $from,
                            'end_time' => $to,
                            'status' => 'active'
                        ];
                        BookingController::create($data);
                        $user->clearState();
                        $bot->sendMessage($chat_id, "забронировано");
                        break;
                }
            }, function () {
                return true;
            });

            $bot->run();

        } catch (\Throwable $e) {
            $data = date('Y-m-d H:i:s', time()) . ' ' . $e->getMessage() . ' ' .
                ' ' . $e->getLine() . ' ' . $e->getFile() . PHP_EOL;
            Logger::log($data);
        }

    }
}