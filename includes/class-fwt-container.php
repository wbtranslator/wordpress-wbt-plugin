<?php

class FwtContainer
{
    public function set($name, $value)
    {
        $this->$name = $value;
    }

    public function get($name)
    {
        return isset($name) ? $this->$name : null;
    }
}