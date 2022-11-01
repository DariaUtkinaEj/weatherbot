<?php

require_once __DIR__ . '/vendor/autoload.php';

use Telegram\Bot\Api;

$token = '5654054564:AAGL4v5g2DbvH_9AzFoC2lx8BsHkz1dSx2w';
$weather_token = '5e1ce895b1f7950c8267adecc8ce4989';
$weather_url = "https://api.openweathermap.org/data/2.5/weather?appid={$weather_token}&units=metric&lang=ru";

//https://api.openweathermap.org/data/2.5/weather?appid=5e1ce895b1f7950c8267adecc8ce4989&lang=ru&units=metric&q=London

$telegram = new Api($token);

$update = $telegram->getWebhookUpdates();

//file_put_contents(__DIR__ . '/logs.txt', print_r($update, 1), FILE_APPEND);

$chat_id = $update['message']['chat']['id'] ?? '';
$text = $update['message']['text'] ?? '';

if ($text == '/start') {
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Привет, {$update['message']['chat']['first_name']}! Я бот-синоптик, который подскажет вам погоду в любом городе мира. Для получения погоды отправьте геолокацию (доступно с мобильных устройств). \nТакже возможно указать город в формате: <b>Город</b> или в формате <b>Город,код страны</b>. \nПримеры: <b>London</b>, <b>London,uk</b>, <b>Kiev,ua</b>, <b>Киев</b>",
        'parse_mode' => 'HTML',
    ]);
} elseif (!empty($text)) {
    $weather_url .= "&q={$text}";
    $res = json_decode(file_get_contents($weather_url));
} elseif (isset($update['message']['location'])) {
    $weather_url .= "&lat={$update['message']['location']['latitude']}{$text}&lon={$update['message']['location']['longitude']}";
    $res = json_decode(file_get_contents($weather_url));
}

if (isset($res)) {
    if (empty($res)) {
        $response = $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Укажите корректный формат.',
        ]);
    } else {
        $t = round($res->main->temp);
        $answer = "<u>Информация о погоде:</u>\nГород: <b>{$res->name}</b>\nСтрана: <b>{$res->sys->country}</b>\nПогода: <b>{$res->weather[0]->description}</b>\nТемпература: <b>{$t}℃</b>";

        $response = $telegram->sendPhoto([
            'chat_id' => $chat_id,
//        'photo' => "https://openweathermap.org/img/wn/{$res->weather[0]->icon}@4x.png",
            'photo' => "icons/{$res->weather[0]->icon}.png",
            'caption' => $answer,
//        'text' => $answer,
            'parse_mode' => 'HTML',
        ]);
    }
}
