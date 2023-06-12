<?

class formTest
{
    public $top = "<html><head></head><body>";
    public $content = "<form enctype=\"multipart/form-data\" action=\"/api/v2.3/letters\"  method=\"POST\"><input type=\"file\" name=\"file\">
    <input type=\"submit\" value=\"Отправить\"></form>";
    public $bottom = "</body></html>";
    public static function html()
    {
        $ft = new FormTest();
        return $ft->top . $ft->content . $ft->bottom;
    }
}
