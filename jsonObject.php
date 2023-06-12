<?
class jsonObject
{
    private $jTxt;
    private $tdata = [];
    private $typeof = ["images", "pdf", "onlineDeliveryPdf", "xml"]; //формат вложений
    private $addresStruct = ["city", "postalCode","region"];//Обязательно в адресе
    private $senderI = ["code", "name", "departmentCode", "departmentName"]; //обязательные поля в отправителе
    private $recipientI = ["countryCodeOKSM"]; //обязательные поля в получателе
    function __construct(string $json = '', bool $debug = false)
    {
        $this->tdata["name"] = "имя параметра";
        $this->tdata["value"] = "Значение параметра";
        $this->tdata["message"] = "Выводимое сообщение";
        $this->tdata["valid"] = true; //по умолчанию валидно
        $this->jTxt = $json;
    }
    public function parce()
    {
        if (is_array(json_decode($this->jTxt, true))) {
            $content = [];
            /* Создаем в базе докумени, который проверяем с его raw данными*/
            foreach (json_decode($this->jTxt, true) as $key => $row) {
                switch ($key) {
                    case "mailId":
                        $d = $this->mailId($row);
                        $content[$d["name"]] = $d;
                        break;
                    case "mailRank":
                        $d = $this->mailRank($row);
                        $content[$d["name"]] = $d;
                        break;
                    case "type":
                        $d = $this->type($row);
                        $content[$d["name"]] = $this->type($row);
                        break;
                    case "templateId":
                        $d = $this->templateId($row);
                        $content[$d["name"]] = $d;
                        break;
                    case "attachments":
                        $d = $this->attachments($row);
                        $content[$d["name"]] = $d;
                        break;
                    case "document":
                        $d = $this->document($row);
                        $content[$d["name"]] = $d;
                        break;
                    case "charge":
                        $d = $this->charge($row);
                        $content[$d["name"]] = $d;
                        break;
                    case "sender":
                        $d = $this->sender($row);
                        $content[$d["name"]] = $d;
                        break;
                    case "recipient":
                        $d = $this->recipient($row);
                        $content[$d["name"]] = $d;
                        break;
                }
                /*сохраняем в базу value валидного поля с привязкой к текущему документу*/
            }
            return $content;
        } else {
            return "JSON не правильного формата";
        }
    }
    private function modifTdata(string $name = '', $value = '', string $message = '', bool $valid = false)
    {
        return [
            "name" => $name,
            "value" => $value,
            "message" => $message,
            "valid" => $valid
        ];
    }
    private function mailId($value)
    {
        //Трек-номер внутреннего почтового отправления обычно состоит из 13 символов (внутрироссийский идентификатор состоит из 14 цифр).
        $count = strlen($value);
        if ($count >= 13 && $count <= 14) return $this->modifTdata("mailId", $value, "Терек-номер валиден и содержит " . $count . " символов", true);
        else return $this->modifTdata("mailId", $value, "Терек-номер не валиден и содержит " . $count . " символов", true);
    }
    private function mailRank($value)
    {
        //Код разряда почтового отправления
        if (gettype($value) == "integer") {
            $valid = true;
            $type = "";
            switch ($value) {
                case "4":
                    $type = "судебное";
                    break;
                case "8":
                    $type = "административное";
                    break;
                case "0":
                    $type = "остальные";
                    break;
                default:
                    $type = "не соответствует формату  РТМ-0002";
                    $valid = false;
            }
            return $this->modifTdata("mailRank", $value, "Формат: " . $type, $valid);
        } else {
            return $this->modifTdata("mailRank", $value, "Формат: не соответствует формату  РТМ-0002 ", false);
        }
    }
    //В php 7.3 Нет нормальной поддержки Enum - делаю на основе значения
    private function type($value)
    {
        $valid = true;
        $message = "Тип ";
        switch ($value) {
            case "REGULAR":
                $message .= "REGULAR";
                break;
            case "REGISTERED":
                $message .= "REGISTERED";
                break;
            default:
                $message .= " не определен";
                $valid = false;
        }
        return $this->modifTdata("type", $value, $message, $valid);
    }
    private function templateId($value)
    {
        $data['name'] = "templateId";
        return $this->modifTdata("templateId", $value, "Не понятно какая должна быть валидация", true);
    }

    private function attachments($value)
    {
        if (gettype($value) == "array") {
            $message = "Воложения валидны";
            $valid = true;
            foreach ($value as $key => $row) {
                if (!in_array($key, $this->typeof)) {
                    $message = "Вложения не валидны";
                    $valid = false;
                    break;
                }
            }
            return $this->modifTdata("attachments", $value, $message, $valid);
        } else {
            return $this->modifTdata("attachments", $value, "Вложения не соответствуют формату", false);
        }
    }
    private function document($value)
    {
        return $this->modifTdata("document", $value, "Не проверяем по усломиям задания", true);
    }
    private function charge($value)
    {
        return $this->modifTdata("charge", $value, "Не проверяем по усломиям задания", true);
    }
    private function sender($value)
    {
        $message = "Отправитель валиден";
        $valid = true;

        foreach($this->senderI as $row){
            if (!array_key_exists($row, $value)) {
                $message = "Отправитель не валиден / Не найден: ".$row;
                $valid = false;
                break;
            }
        }

        foreach($this->addresStruct as $row){
            if (!array_key_exists($row, $value["returnAddress"] )) {
                $message = "Отправитель не валиден / Ошибка в адресе / Не найден: ".$row;
                $valid = false;
                break;
            }
        }
        return $this->modifTdata("sender", $value, $message, $valid);
    }
    
    private function recipient($value)
    {
        $message = "Получатель валиден";
        $valid = true;

        foreach($this->recipientI as $row){
            if (!array_key_exists($row, $value)) {
                $message = "Получатель не валиден / Не найден: ".$row;
                $valid = false;
                break;
            }
        }

        return $this->modifTdata("recipient", $value, $message, $valid);
    }
}