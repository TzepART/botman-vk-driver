<?php


namespace VkBotMan\Conversation;


use VkBotMan\Extensions\KeyboardButton;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;

class EntryPointConversation extends AbstractConversation
{
    const FORCE_MODE_TEXT = '/promo';
    const INTRO_TEXT = <<<EOL
Привет! Хочешь получить шанс выиграть стайлер для укладки волос или билет на концерт в Москве? 
Прими участие в акции от «Магнит Косметик»!
EOL;

    public function entryPoint($text = self::INTRO_TEXT)
    {
        $question = Question::create($text)
            ->addButtons([
                KeyboardButton::create('Да, конечно!')->value('yes')->color('positive'),
                KeyboardButton::create('Нет, спасибо!')->value('no')->color('default'),
            ]);

        $this->ask($question, function (Answer $answer) {

            if ($answer->isInteractiveMessageReply()) {
                switch ($answer->getValue()) {
                    case 'yes':
                        $this->promoMode();
                        break;
                    default:
                        $this->returnToHumanMode();
                        break;
                }
            } else {
                $textAnswer = mb_strtolower($answer->getMessage()->getText());

                switch ($textAnswer) {
                    case 'да':
                        $this->promoMode();
                        break;
                    case 'нет':
                        $this->returnToHumanMode();
                        break;
                    default:
                        $this->getBot()->typesAndWaits(2);
                        $this->entryPoint(AbstractConversation::MISUNDERSTANDING_ERROR);
                        break;
                }
            }
        });
    }

    public function promoMode()
    {
        $this->bot->typesAndWaits(2);

        $this->say(<<<EOL
Чтобы принять участие в акции, с 24 октября по 20 ноября 2018 года 
1. Купи два продукта Wellaflex в любом магазине сети «Магнит Косметик». 
2. Загрузи сюда фотографию чека. 
3. Выполни небольшое творческое задание.
EOL
        );

        $this->bot->typesAndWaits(4);
    }

    public function run()
    {
        $convers = $this->getBot()->userStorage()->get('convers');

        if (null !== $convers) {
            $this->getBot()->startConversation(new $convers);
            $this->getBot()->userStorage()->save(['convers' => self::class]);
        } else {
            return $this->entryPoint();
        }

        return true;
    }
}