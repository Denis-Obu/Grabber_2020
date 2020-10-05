<?php

namespace app\components;

use Exception;
use GuzzleHttp\Client;

class PageLoader
{
    private $links = array();
    private $saveDir;
    private $urlName;
    private $subDir = "/files";


    function __construct($urlName, $dir = "/protected/pages")
    {
        $this->saveDir = __DIR__ . '/..' . $dir;
        $this->urlName = $urlName;
    }

    // получить содержимое сайта через CURL

    static $file_size_ext =
        [   //изображения
            'jpeg' => 2000, //2000 kb
            'gif' => 1000, //1000 kb
            'png' => 2000, //2000 kb
            'svg' => 2000, //2000 kb
            //фудио
            'mp3' => 5 * 1024, //5 MB
            'wav' => 5 * 1024, //5 MB
            //видео
            'mp4' => 7 * 1024, //7 MB
            'ogg' => 7 * 1024, //7 MB
            //другой
            'js' => 3000, //3000 kb
            'css' => 1000, //1000 kb
        ];

    // получить содержимое сайта через curl (если надумаем использовать, добавить в composer зависимость)
    public static function getSiteContentCurl($url)
    {
        $referer = "http://www.google.com";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36");
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $siteContent = curl_exec($ch);
        curl_close($ch);

        return $siteContent;
    }

    // получить содержимое сайта через Guzzle
    public static function getSiteContentGuzzle($url)
    {
        $client = new Client([
            'base_uri' => $url, // Base URI is used with relative requests
        ]);
        $response = $client->get($url);
        $siteContent = null;
        if ($response->getReasonPhrase() === 'OK') {
            //получим тело
            $siteContent = $response->getBody();
        }
        return $siteContent;
    }

    public static function getSiteContent($url)
    {
        return PageLoader::getSiteContentGuzzle($url);
    }

    //получить массив ресурсов регулярками
    public static function parseContentReg($siteContent)
    {
        $files = [];
        preg_match_all('#(img|src|href)=("|\')[^"\'>]+#i', $siteContent, $matches);
        unset($siteContent); // удалим переменную

        $data = preg_replace('#(img|src|href)("|\'|="|=\')(.*)#i', "$3", $matches[0]);
        foreach ($data as $url) {
            $info = pathinfo($url);
            //отбираем в  массив только файлы с указанным расширением
            if (isset($info['extension'])) {
                if (array_key_exists($info['extension'], self::$file_size_ext)) {
                    array_push($files, $url);
                }
            }
        }
        $files = array_unique($files);

        return $files;
    }

    // получить по базовму адресу и массиву ссылок массив абсолютных ссылок
    public static function getAbsoluteUrl($files, $url)
    {
        $ar_url = parse_url($url); //Разбираем URL и возвращает его компоненты
        $root_url = $ar_url['scheme'] . "://" . $ar_url['host'];
        $result = array();
        if ($url === $root_url) {
            $dirname = $root_url;
        } else {
            $info = pathinfo($url);
            $dirname = $info['dirname'];
        }

        $i = 0;
        foreach ($files as $elem) {
            $i++;
            $info = pathinfo($elem);

            $row = ['rowUrl' => $elem, 'basename' => $info['basename'], 'loaded' => false, 'extension' => $info['extension']];
            if (preg_match('#^(http://|https://)#i', $elem)) {
                $row['absoluteUrl'] = $elem;
            } elseif (strpos($elem, "/") === 0) {
                $row['absoluteUrl'] = $root_url . $elem;
            } elseif (preg_match('#(\.\./)#i', $elem)) { //относительные ссылки
                $row['absoluteUrl'] = null;
            } else {
                $row['absoluteUrl'] = $dirname . "/" . $elem;
            }
            array_push($result, $row);
        }

        //переименовать файлы, если пути разные а имя одинаковое
        for ($i = 0; $i < count($result); $i++) {
            //проверяем есть ли файлы с таким именем но другим абсолютным адрсом
            for ($j = $i + 1; $j < count($result); $j++) {
                if (($result[$i]['basename'] === $result[$j]['basename']) and !($result[$i]['absoluteUrl'] === $result[$j]['absoluteUrl'])) {
                    //получаем свободное имя
                    $new_basename = self::get_unic_name($result[$i]['basename'], $result);
                    //переименовываем все файлы с такиим абсолютным адресом
                    for ($k = $j; $k < count($result); $k++) {
                        if ($result[$k]['absoluteUrl'] === $result[$j]['absoluteUrl']) {
                            $result[$k]['basename'] = $new_basename;
                        }
                    }
                }
            }
        }
        return $result;
    }

    //сформировать новое имя
    private static function get_unic_name($cur_name, $ar)
    {
        $ar_name = array_column($ar, 'basename');
        $i = 0;
        do {
            $i++;
        } while (in_array("($i)" . $cur_name, $ar_name) and $i < 100);

        return "($i)" . $cur_name;
    }

    public function doLoad($url)
    {
        // скачиваем страницу
        $body = self::getSiteContent($url);

        //создаем папку в диреекктории
        $pathname = $this->saveDir . '/' . $this->urlName;
        mkdir($pathname, 0777, true);

        $pathname_f = $pathname . $this->subDir;
        mkdir($pathname_f, 0777);

        $pathname_f = $pathname_f . '/';
        //парсим страницу получаем линки
        $links = self::parseContentReg($body);
        $links1 = self::getAbsoluteUrl($links, $url);

        //пробуем скачивать
        for ($i = 0; $i < count($links1); $i++) {
            try {
                //проверим размер файла
                $data = get_headers($links1[$i]['absoluteUrl'], true);
                $size = isset($data['Content-Length']) ? (int)$data['Content-Length'] : 0;
                $links1[$i]['size'] = $size;

                if ($size < self::$file_size_ext[$links1[$i]['extension']] * 1024) {
                    $img = file_get_contents($links1[$i]['absoluteUrl']);
                    if ($img) {
                        $links1[$i]['loaded'] = 'sucsess';
                        file_put_contents($pathname_f . $links1[$i]['basename'], $img);
                    }
                } else {
                    $links1[$i]['loaded'] = 'error: ' . $links1[$i]['extension'] . ' BIG SIZE ' . ($size / 1024) . ' KB max size ' . self::$file_size_ext[$links1[$i]['extension']];
                }
            } catch (Exception $e) {
                $links1[$i]['loaded'] = 'error:' . substr($e->getMessage(), 0, 20);
                // возможно, сделать логгирование для ошибок скачивания
            }
        }

        //формируем массивы строк для замены
        $ar = []; // что подменяем
        $ar_n = []; // на что подменяем
        foreach ($links1 as $elem) {
            if ($elem['loaded'] === 'sucsess') {
                array_push($ar, '="' . $elem['rowUrl'] . '"');
                array_push($ar_n, '="' . $this->subDir . '/' . $elem['basename'] . '"');

                array_push($ar, "='" . $elem['rowUrl'] . "'");
                array_push($ar_n, '=\'' . $this->subDir . '/' . $elem['basename'] . '\'');
            }
        }

        //подменяем ссылки в файле на локальные
        $body = str_replace($ar, $ar_n, $body);
        // сохраняем подмененный файл
        file_put_contents($pathname . '/' . $this->urlName . ".html", $body);

        return $links1;
    }
}