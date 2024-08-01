<?php
namespace Entities;

use Exceptions\InvalidEntityKeyException;

class Entity
{
    public function set(array $data): self
    {
        foreach ($data as $key => $value) {
            throw_if(
                !property_exists($this, $key),
                new InvalidEntityKeyException('Invalid ' . $this->getEntityName() . " entity key $key")
            );

            $this->$key = $value;
        }

        return $this;
    }

    public function get()
    {
        return get_object_vars($this);
    }

    public function __set($key, $value)
    {
        $this->set([$key => $value]);
    }

    private function getEntityName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
