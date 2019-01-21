<?php


namespace VkBotMan;


use VkBotMan\Conversation\EntryPointConversation;
use VkBotMan\Drivers\VkAttachmentDriver;
use VkBotMan\Drivers\VkDriver;
use BotMan\BotMan\Cache\SymfonyCache;
use BotMan\BotMan\Drivers\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

class BotManHandler
{
    private $logger;
    private $entityManager;
    private $mediaManager;
    private $request;
    private $vkApiHandler;

    public function __construct(
        LoggerInterface $logger,
        ManagerInterface $mediaManager,
        EntityManagerInterface $entityManager,
        Request $request,
        VkApiHandler $vkApiHandler)
    {
        $this->logger = $logger;
        $this->mediaManager = $mediaManager;
        $this->entityManager = $entityManager;
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
            $this->entityManager,
            $this->mediaManager,
            $this->logger,
            $this->vkApiHandler
        );

        $botman = $factory::make($config, new SymfonyCache($adapter), $this->request);

        return $botman;
    }

    public function attachConversation()
    {
        $botman = $this->getBotman();

        $botman->hears('.*', function (BotMan $bot) {
            $bot->startConversation(new EntryPointConversation());
        });

        $botman->listen();
    }
}