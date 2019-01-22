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
            ->say($this->getReturnToHumanModeMessage())
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

    /**
     * @return string
     */
    abstract protected function getReturnToHumanModeMessage() : string;
}