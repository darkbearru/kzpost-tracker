<?php

class Tracker
{
    protected object $dictionary;
    protected string $trackNumber;

    public function __construct()
    {
        $this->dictionary = getDictionary(REMOTE_URL . REMOTE_DICTIONARY);
        $this->trackNumber = $this->checkTrackNumber($_REQUEST['track_number'] ?? '');
    }

    private function checkTrackNumber(string $number): string
    {
        if (preg_match('/^[A-Z]{2}\d{9}[A-Z]{2}$/usm', $number)) return $number;
        return false;
    }

    public function run(): string
    {
        if ($this->trackNumber) {
            return showHTML(
                ['track' => $this->getTrackInfo(REMOTE_URL . $_REQUEST['track_number'])],
                '_track_info.html'
            );
        }
        return showHTML(['dictionary' => json_encode($this->dictionary, JSON_UNESCAPED_UNICODE)]);
    }

    protected function getTrackInfo($url): string
    {
        try {
            $trackInfo = json_decode(file_get_contents($url));
        } catch (Exception $e) {
            showError(501, 'Ошибка получения справочника');
        }
        return setParagraphs($this->Humanization($trackInfo)) .
            "<div class='details'>" .
            setParagraphs($this->Details($trackInfo)) .
            "</div>";

    }

    protected function Humanization(object $trackInfo): string
    {
        $date = strtotime($trackInfo->origin->date);
        $text = mb_ucfirst($trackInfo->package_type) .
            " ({$trackInfo->category}) весом в {$trackInfo->weight} кг " .
            "отправлена " . date('d.m.Y', $date) . " в " . date("H:i", $date) .
            " " . $this->humanizeDeliveryMethod($trackInfo->delivery_method) . " транспортом от " .
            $trackInfo->sender->name . " ({$trackInfo->sender->country}, {$trackInfo->sender->address})" .
            " для " .
            $trackInfo->receiver->name . " ({$trackInfo->receiver->country}, {$trackInfo->receiver->address})";
        $text .= PHP_EOL;
        return $text;
    }

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

    protected function Details(object $trackInfo): string
    {
        $text = "Посылка: №" . $trackInfo->trackid . PHP_EOL;
        $text .= "Статус:" . $trackInfo->status .
            (!empty($trackInfo->status) ? " (" . ($this->dictionary[$trackInfo->status_code] ?? 'undetected') . ")" : "") .
            PHP_EOL;
        return $text;
    }
}