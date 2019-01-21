<?php

namespace VkBotMan\Extensions;

use BotMan\BotMan\Interfaces\QuestionActionInterface;

/**
 * Class KeyboardButton.
 */
class KeyboardButton implements \JsonSerializable, QuestionActionInterface
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $value;

    /** @var array */
    protected $additional = [];

    /** @var string */
    protected $color;

    /**
     * @param $text
     * @return KeyboardButton
     */
    public static function create($text)
    {
        return new self($text);
    }

    /**
     * KeyboardButton constructor.
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Set the button additional parameters to pass to the service.
     *
     * @param array $additional
     * @return $this
     */
    public function additionalParameters(array $additional)
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Set the button value.
     *
     * @param string $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = $value;

        return $this;
    }

    public function color($color)
    {
        $this->color = $color;
        return $this;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'action' => [
                'type' => 'text',
                'payload' => "{\"button\": \"{$this->value}\"}",
                'label' => $this->text,
            ],
            'color' => $this->color,
            'additional' => $this->additional,
        ];
    }
    /**
     * Specify data which should be serialized to JSON.
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return json_encode($this->toArray());

//        return Collection::make([
//            'url' => $this->url,
//            'callback_data' => $this->callbackData,
//            'request_contact' => $this->requestContact,
//            'request_location' => $this->requestLocation,
//            'text' => $this->text,
//        ])->filter()->toArray();
    }
}
