<?php

namespace Wiry;

abstract class Frame
{
    protected $id;

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    protected $session;

    public function setSession(string $session)
    {
        $this->session = $session;
    }

    public function getSession(): string
    {
        return $this->session;
    }

    protected $namespace;

    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    abstract public function render();
}
