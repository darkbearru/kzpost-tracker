<?php

/**
 * getDictionary
 * Получение данных по справочнику статусов посылок
 * @param $url
 * @return array
 */
function getDictionary($url): object
{
    if (file_exists(CACHE_FILE_NAME))
        return json_decode(file_get_contents(CACHE_FILE_NAME));
    try {
        $json = json_decode(file_get_contents($url));

        file_put_contents(CACHE_FILE_NAME, json_encode($json, JSON_UNESCAPED_UNICODE));
    } catch (Exception $e) {
        showError(501, 'Ошибка получения справочника');
    }
    return $json;
}


function setParagraphs(string $text): string
{
    return "<p>" . str_replace(PHP_EOL, "</p>" . PHP_EOL . "<p>", $text) . "</p>";
}

/**
 * showHTML
 * отображение HTML шаблона
 * @param string $template
 * @param array|null $params
 * @return string
 */
function showHTML(array $params = null, string $template = '_index.html'): string
{
    try {
        $file = file_get_contents(TEMPLATE_DIR . $template);
    } catch (Exception $e) {
        showError(501, 'Ошибка чтения шаблона');
    }
    if ($params) {
        foreach ($params as $param => $value) {
            $file = preg_replace("/{{\s+$param\s+}}/ui", $value, $file);
        }
    }
    return $file;
}

/**
 * showError
 * Вывод ошибки
 * @param $code
 * @param $message
 * @return void
 */
function showError($code, $message): void
{
    if ($_SERVER['SERVER_PROTOCOL'] ?? false) {
        header($_SERVER['SERVER_PROTOCOL'] . " {$code} " . getErrorMessage($code));
    }
    echo $message;
    exit;
}

/**
 * getErrorMessage
 * Получение описания ошибки по её коду
 * @param int $code
 * @return string
 */
function getErrorMessage(int $code): string
{
    $errorMessages = [
        3 => 'Multiple Choice',
        4 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        405 => 'Not Found',
        406 => 'Method Not Allowed',
        5 => 'Internal Server Error'
    ];

    if (!empty($errorMessages[$code])) return $errorMessages[$code];
    $code = (int)($code / 100);
    if (!empty($errorMessages[$code])) return $errorMessages[$code];
    return "Unrecognized error";
}

if (!function_exists('mb_ucfirst') && function_exists('mb_substr')) {
    function mb_ucfirst($string): string
    {
        return mb_strtoupper(
                mb_substr($string, 0, 1, 'utf-8'),
                'utf-8') .
            mb_substr($string, 1);
    }
}