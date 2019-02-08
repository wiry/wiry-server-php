<?php

namespace Wiry;

use Predis\Client;
use Ramsey\Uuid\Uuid;

class Server
{
    protected $redis;
    protected $session;
    protected $namespace;

    public function __construct(Client $redis = null)
    {
        if (!$redis) {
            $redis = new Client();
        }

        $this->redis = $redis;
    }

    public function setSession(string $session)
    {
        $this->session = $session;
    }

    public function getSession(): string
    {
        if (!$this->session) {
            $this->session = $this->getId();
        }

        return $this->session;
    }

    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): string
    {
        if (!$this->namespace) {
            $this->namespace = "wiry";
        }

        return $this->namespace;
    }

    private function getId(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function attach(Frame $frame)
    {
        $id = $this->getId();
        $session = $this->getSession();
        $namespace = $this->getNamespace();

        $frame->setId($id);
        $frame->setSession($session);
        $frame->setNamespace($namespace);

        $objects = $this->getObjects($session);
        $objects[$id] = $frame;

        $this->setObjects($session, $objects);
    }

    public function trigger(string $id, string $method)
    {
        $session = $this->getSession();
        $objects = $this->getObjects($session);

        $object = $objects[$id];

        if (!$object) {
            return null;
        }

        $parts = explode(":", $method);
        $method = array_shift($parts);

        $object->$method(...$parts);

        $this->setObjects($session, $objects);

        return $object->render();
    }

    protected function getObjects(string $session)
    {
        $objects = $this->retrieve($session);

        if ($objects) {
            $objects = unserialize($objects);
        } else {
            $objects = [];
        }

        return $objects;
    }

    protected function retrieve($key)
    {
        return $this->redis->get($key);
    }

    protected function setObjects(string $session, array $objects)
    {
        $this->store($session, serialize($objects));
    }

    protected function store($key, $value)
    {
        $this->redis->set($key, $value);
    }
}
