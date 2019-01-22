<?php


namespace VkBotMan\Model;


use VkBotMan\BotMan;
use VkBotMan\VkApiHandler;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

/**
 * Class AbstractConversation
 * @package VkBotMan\Model
 */
abstract class AbstractConversation extends Conversation
{
    const MISUNDERSTANDING_ERROR = '&#128161;Ответь «да» или «нет» или используй нужные кнопки на клавиатуре Вконтакте.';

    /**
     * @var BotMan
     */
    protected $bot;

    /**
     *
     */
    protected function retry()
    {
        $this->bot->startConversation(new $this);
    }

    /**
     *
     */
    public function returnToHumanMode()
    {
        $this->getBot()->typesAndWaits(2);
        $this->turnOffBot();

        $this
            ->say(<<<EOL
Хорошо, ты можешь задать вопрос администратору группы Wella.
А если захочешь принять участие в акции, напиши в этот чат сообщение с текстом
/promo
EOL
)
            ->stopsConversation(new IncomingMessage('', '', ''));
    }

    /**
     *
     */
    public function turnOffBot()
    {
        // TODO do something
    }

    /**
     * @param null $classname
     */
    public function saveConversationState($classname = null)
    {
        $conversation = static::class;

        if (null !== $classname){
            $conversation = $classname;
        }

        $this->getBot()->userStorage()->save(['convers' => $conversation]);
    }

    /**
     * @return VkApiHandler
     */
    final public function getVkApiHandler()
    {
        return $this->bot->getVkApiHandler();
    }
}