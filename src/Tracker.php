<?php

/**
 * Tracker
 * Класс получения информации по треку и обработки его отображения
 */
class Tracker
{
    /**
     * Справочник определений кодов статуса
     * @var object|array
     */
    protected object $dictionary;
    /**
     * Номер трекинга
     * @var string
     */
    protected string $trackNumber;

    /**
     *
     */
    public function __construct()
    {
        $this->trackNumber = $this->checkTrackNumber($_REQUEST['track_number'] ?? '');
    }

    /**
     * Проверяем корректность переданного номера
     * @param string $number
     * @return string
     */
    private function checkTrackNumber(string $number): string
    {
        if (preg_match('/^[A-Z]{2}\d{9}[A-Z]{2}$/usm', $number)) return $number;
        return false;
    }

    /**
     * Получение данных и отображения результата
     * @return string
     */
    public function run(): string
    {
        if ($this->trackNumber) {
            $this->dictionary = getDictionary(REMOTE_URL . REMOTE_DICTIONARY);
            return showHTML(
                $this->getTrackInfo(REMOTE_URL . $_REQUEST['track_number']),
                '_track_info.html'
            );
        }
        return showHTML();
    }

    /**
     * Получение информации по трек номеру
     * @param $url
     * @return array
     */
    protected function getTrackInfo($url): array
    {
        try {
            $trackInfo = json_decode(file_get_contents($url));
        } catch (Exception $e) {
            showError(501, 'Ошибка получения справочника');
        }
        return [
            'human-like-text' => setParagraphs($this->Humanization($trackInfo)),
            'trackinfo' => setParagraphs($this->Details($trackInfo))
        ];
    }

    /**
     * Приведение в удобный для чтения вид, информации о посылки
     * @param object $trackInfo
     * @return string
     */
    protected function Humanization(object $trackInfo): string
    {
        $date = strtotime($trackInfo->origin->date);
        $text = mb_ucfirst($trackInfo->package_type) .
            " ({$trackInfo->category}) весом в {$trackInfo->weight} кг " .
            $this->detectTrackAction($trackInfo->package_type) . " " .
            date('d.m.Y', $date) . " в " . date("H:i", $date) .
            " " . $this->humanizeDeliveryMethod($trackInfo->delivery_method) . " транспортом" . PHP_EOL;
        $text .= "От " .
            $this->personInfo($trackInfo->sender) .
            " для " .
            $this->personInfo($trackInfo->receiver);
        $text .= PHP_EOL;
        return $text;
    }

    private function detectTrackAction(string $packageType): string
    {
        $packageType = mb_strtolower($packageType, 'utf-8');
        if (preg_match('/письмо/us', $packageType)) return "отправлено";
        if (preg_match('/посылка/us', $packageType)) return "отправлена";
        return "отправлен";
    }

    /**
     * Упрощенная коррекция окончаний для метода доставки
     * @param string $method
     * @return string
     */
    private function humanizeDeliveryMethod(string $method): string
    {
        $search = [
            "/наземный/ui",
            "/авиационный/ui",
            "/железнодорожный/ui"
        ];
        $replace = [
            "наземным",
            "авиа",
            "железнодорожным"
        ];
        return preg_replace($search, $replace, mb_strtolower($method, 'utf-8'));
    }

    /**
     * Отображение информации о персоне (отправитель/получатель)
     * @param object $person
     * @return string
     */
    private function personInfo(object $person): string
    {
        return "{$person->name}  <i>({$person->country}, {$person->address})</i>";
    }

    /**
     * Формирование детальной информации
     * @param object $trackInfo
     * @return string
     */
    protected function Details(object $trackInfo): string
    {
        $text = "Посылка: №{$trackInfo->trackid}" . PHP_EOL;
        $text .= "Статус: " . $trackInfo->status .
            (!empty($trackInfo->status) ? " (" . $this->statusCode($trackInfo->status_code) . ")" : "") .
            PHP_EOL;
        $text .= "Тип отправления: {$trackInfo->package_type}" . PHP_EOL;
        $text .= "Категория: {$trackInfo->category}" . PHP_EOL;
        $text .= "Вид доставки: {$trackInfo->delivery_method}" . PHP_EOL;
        $text .= "Вес отправления: {$trackInfo->weight} кг" . PHP_EOL;
        $text .= "Срок хранения: {$trackInfo->storage_period}" . PHP_EOL;

        $text .= "Отправитель: {$this->personInfo($trackInfo->sender)}" . PHP_EOL;
        $text .= "Отделение отправления: {$this->departmentInfo($trackInfo->origin)}" . PHP_EOL;
        $text .= "Получатель: {$this->personInfo($trackInfo->receiver)}" . PHP_EOL;
        $text .= "Отделение назначения: {$this->departmentInfo($trackInfo->last)}" . PHP_EOL;
        $text .= PHP_EOL;

        if (!empty($trackInfo->delivery->date)) {
            $text .= "<h4>Доставка:</h4>" . PHP_EOL;
            $text .= "Дата: " . $trackInfo->delivery->time . " " .
                date("d.m.Y", strtotime($trackInfo->delivery->date)) . PHP_EOL;
            $text .= "Город: {$trackInfo->delivery->city}" . PHP_EOL;
            $text .= "Индекс: {$trackInfo->delivery->postindex}" . PHP_EOL;
            $text .= "Адрес: {$trackInfo->delivery->address}" . PHP_EOL;
            $text .= "Телефон: {$trackInfo->delivery->phone}" . PHP_EOL;
            $text .= "Отделение: {$trackInfo->delivery->dep_name}" . PHP_EOL;
        }

        return $text;
    }

    /**
     * Получение текста кода статуса
     * @param $code
     * @return string
     */
    private function statusCode($code): string
    {
        if ($this->dictionary->{$code} ?? false)
            return $this->dictionary->{$code}->title_ru;
        return '';
    }

    /**
     * Формирование информации об отделении
     * @param object $department
     * @return string
     */
    private function departmentInfo(object $department): string
    {
        return "{$department->postindex}, {$department->dep_name}  " .
            "<i>/ " . date('H:i d.m.Y', strtotime($department->date)) . "</i>";
    }
}