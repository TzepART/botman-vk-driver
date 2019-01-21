<?php


namespace VkBotMan;


use VkBotMan\Conversation\EntryPointConversation;
use VkBotMan\Drivers\VkAttachmentDriver;
use VkBotMan\Drivers\VkDriver;
use BotMan\BotMan\Cache\SymfonyCache;
use BotMan\BotMan\Drivers\DriverManager;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use VkBotMan\Model\AbstractConversation;

/**
 * Class BotManHandler
 * @package VkBotMan
 */
class BotManHandler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var VkApiHandler
     */
    private $vkApiHandler;

    /**
     * BotManHandler constructor.
     * @param Request $request
     * @param VkApiHandler $vkApiHandler
     */
    public function __construct(
        Request $request,
        VkApiHandler $vkApiHandler)
    {
        $this->request = $request;
        $this->vkApiHandler = $vkApiHandler;
    }

    /**
     * @return \BotMan\BotMan\BotMan
     */
    public function getBotman()
    {
        DriverManager::loadDriver(VkDriver::class);
        DriverManager::loadDriver(VkAttachmentDriver::class);

        $adapter = new FilesystemAdapter('', 0, __DIR__ . '/../var/botman/conversations');

        $config = [
            'config' => [
                'conversation_cache_time' => 60 * 24 * 30 * 3,
                'user_cache_time' => 60 * 24 * 30 * 3
            ],
        ];

        $factory = new BotManFactory(
            $this->vkApiHandler
        );

        $botman = $factory::make($config, new SymfonyCache($adapter), $this->request);

        return $botman;
    }

    /**
     *
     */
    public function attachConversation(AbstractConversation $conversation)
    {
        $botman = $this->getBotman();

        $botman->hears('.*', function (BotMan $bot) {
            $bot->startConversation($conversation);
        });

        $botman->listen();
    }
}