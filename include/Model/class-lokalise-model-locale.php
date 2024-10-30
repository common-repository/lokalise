<?php

class Lokalise_Model_Locale implements JsonSerializable
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $code;

    /**
     * @param string $name
     * @param string $code
     */
    public function __construct($name, $code)
    {
        $this->name = $name;
        $this->code = $code;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
        ];
    }
}
