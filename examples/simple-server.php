<?php

require __DIR__ . "/../vendor/autoload.php";

// 1. create a new Frame subclass...

class MyFrame extends Wiry\Frame
{
    protected $count = 0;

    public function increment()
    {
        $this->count += 1;
    }

    public function decrement()
    {
        $this->count -= 1;
    }

    public function render()
    {
        $id = $this->getId();
        $session = $this->getSession();
        $namespace = $this->getNamespace();

        $count = $this->count;

        if (!$id || !$session) {
            throw new Exception("you didn't attach this frame");
        }

        $content = file_get_contents(__DIR__ . "/simple-server-view.php");
        $content = str_replace("{count}", $count, $content);
        $content = str_replace("{namespace}", $namespace, $content);

        return "<div data-{$namespace}-id='{$id}' data-{$namespace}-session='{$session}'>{$content}</div>";
    }
}

// 2. create a new Server instance...

$server = new Wiry\Server();
$server->setNamespace("live");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // 3. return an initial view, attaching frames
    //    to the server...

    $namespace = $server->getNamespace();

    if (isset($_GET["{$namespace}-session"])) {
        $session = $_GET["{$namespace}-session"];
        $server->setSession($session);
    }

    $frame = new MyFrame();
    $server->attach($frame);

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");

    print $frame->render();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 4. respond to events by triggering methods on
    //    stored frames...

    $namespace = $server->getNamespace();

    if (isset($_GET["{$namespace}-session"])) {
        $session = $_GET["{$namespace}-session"];
        $server->setSession($session);
    }

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");

    $text = file_get_contents("php://input");
    $json = json_decode($text);

    if ($json->type === "click") {
        print json_encode([
            "type" => "render",
            "data" => [
                "id" => $json->data->id,
                "html" => $server->trigger($json->data->id, $json->data->method),
            ],
        ]);
    }
}
