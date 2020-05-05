<?php

namespace BotMan\Drivers\Vk\Extensions;


use Illuminate\Support\Collection;

/**
 * Class Keyboard.
 */
class Keyboard
{
    const TYPE_KEYBOARD = 'keyboard';
    const TYPE_INLINE = 'inline_keyboard';

    protected $oneTimeKeyboard = false;
    protected $resizeKeyboard = false;

    /**
     * @var array
     */
    protected $rows = [];

    /**
     * @param string $type
     * @return Keyboard
     */
    public static function create($type = self::TYPE_INLINE)
    {
        return new self($type);
    }

    /**
     * Keyboard constructor.
     * @param string $type
     */
    public function __construct($type = self::TYPE_INLINE)
    {
        $this->type = $type;
    }

    /**
     * @param $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function oneTimeKeyboard($active = true)
    {
        $this->oneTimeKeyboard = $active;

        return $this;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function resizeKeyboard($active = true)
    {
        $this->resizeKeyboard = $active;

        return $this;
    }

    /**
     * Add a new row to the Keyboard.
     * @param KeyboardButton[]|array $buttons
     * @return Keyboard
     */
    public function addRow(...$buttons)
    {
        $buttons = json_decode(json_encode($buttons), true);
        foreach($buttons as $key=>$button) {
            $buttons[$key] = json_decode($button, true);
        }
        return $this;
    }

    /**
     * @return false|string
     */
    public function toArray()
    {
        return json_encode(Collection::make([
            'one_time' => $this->resizeKeyboard,
            'buttons' => $this->rows,
            'inline' => ($this->type == 'inline' ? true : false)
        ])->filter());
    }
}
