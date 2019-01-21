<?php


namespace VkBotMan;


use BotMan\BotMan\Interfaces\CacheInterface;
use BotMan\BotMan\Interfaces\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use BotMan\BotMan\Cache\ArrayCache;
use BotMan\BotMan\Storages\Drivers\FileStorage;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Http\Curl;

class BotManFactory extends \BotMan\BotMan\BotManFactory
{
    private static $vkApiHandler;

    public function __construct(
        VkApiHandler $vkApiHandler)
    {
        self::$vkApiHandler = $vkApiHandler;
    }


    /**
     * Create a new BotMan instance.
     *
     * @param array $config
     * @param CacheInterface $cache
     * @param Request $request
     * @param StorageInterface $storageDriver
     * @return \BotMan\BotMan\BotMan
     */
    public static function make(
        array $config,
        CacheInterface $cache = null,
        Request $request = null,
        StorageInterface $storageDriver = null
    )
    {
        if (empty($cache)) {
            $cache = new ArrayCache();
        }
        if (empty($request)) {
            $request = Request::createFromGlobals();
        }
        if (empty($storageDriver)) {
            $storageDriver = new FileStorage(__DIR__ . '/../var/botman/user-storage');
        }

        $driverManager = new DriverManager($config, new Curl());
        $driver = $driverManager->getMatchingDriver($request);

        $botman = new BotMan($cache, $driver, $config, $storageDriver);
        $botman->setVkApiHandler(self::$vkApiHandler);

        return $botman;
    }
}