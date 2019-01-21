<?php


namespace VkBotMan;


/**
 * Class BotMan
 * @package VkBotMan
 * TODO add Entity Manager, Media Manager and Logger
 */
class BotMan extends \BotMan\BotMan\BotMan
{
    /** @var VkApiHandler */
    private $vkApiHandler;

    /**
     * @return VkApiHandler
     */
    public function getVkApiHandler(): VkApiHandler
    {
        return $this->vkApiHandler;
    }

    /**
     * @param VkApiHandler $vkApiHandler
     * @return BotMan
     */
    public function setVkApiHandler(VkApiHandler $vkApiHandler): BotMan
    {
        $this->vkApiHandler = $vkApiHandler;
        return $this;
    }
}