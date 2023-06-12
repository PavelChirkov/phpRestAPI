<?
include("formTest.php");
include("jsonObject.php");
class API
{
    private $debug = false;
    private $url = "";
    private $action = "/api/v2.3/letters";
    private $test = "/form/test";
    private $content_type = "Content-Type: application/json; charset=utf-8";
    public $html = '';

    function __construct()
    {
        $this->url = $_SERVER['REQUEST_URI'];
        $this->debug = false;
    }
    public function index()
    {
        /*В начале проверка токена - но не пишем ее по условию задания*/
        if ($this->url == $this->action) {
            $type = $_FILES["file"]["type"];
            if ($type == "application/json") {
                $json = new jsonObject(file_get_contents($_FILES["file"]["tmp_name"]));
                if ($this->debug) {
                    print "<pre>";
                    $this->content_type = "Content-Type: text/html; charset=UTF-8";

                    print "--------------------<br>";
                    print_r($json->parce());
                    print "</pre>";

                }else{
                    $data["code"] = '200';
                    $data["letter"] = $json->parce();
                    $this->printHtml(json_encode($data));
                }
            } else {
                $this->error();
            }
        } elseif ($this->url == $this->test) {
            $this->content_type = "Content-Type: text/html; charset=UTF-8";
            $this->printHtml(FormTest::html());
        } else {
            header($this->content_type);
            $data = [
                "code" => "403",
                "massage"=> "доступ закрыт"
            ];
            $this->printHtml(json_encode($data));
        }
    }
    private function error(string $message = 'Возникла ошибка')
    {
        header($this->content_type);
        $data = [
            "code" => "401",
            "massage"=> $message
        ];
        print json_encode($data);
    }
    private function printHtml(string $html)
    {
        header($this->content_type);
        print $html;
    }
}