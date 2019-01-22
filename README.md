# Botman VK-driver

[Packagist link](https://packagist.org/packages/tzepart/botman-vk-driver)

Driver for [Botman library](https://botman.io/)

Example Conversation Class

```php
<?php
namespace App\Conversation;

use VkBotMan\Model\AbstractConversation;

/**
 * Class EntryPointConversation
 */
class EntryPointConversation extends AbstractConversation
{
  //... logic your conversation
}
```

Example Using Conversation (Symfony framework)

```php
<?php
namespace App\Controller;

use App\Conversation\EntryPointConversation;
//...

class VkController extends AbstractController
{
    /**
     * @Route("/app/callback", methods={"POST"}, name="app_callback")
     * @param Request $request
     * @return Response
     */
    public function callback(
        Request $request
    )
    {
        $vkApiHandler = new VkApiHandler();
  
        $handler = new BotManHandler(
            $request,
            $vkApiHandler
        );

        $handler->attachConversation(new EntryPointConversation());

        return new Response();
    }
}

```
