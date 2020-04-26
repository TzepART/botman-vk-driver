<?php

namespace BotMan\Drivers\VK;

use BotMan\BotMan\Interfaces\UserInterface;
use BotMan\Drivers\VK\Extensions\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Interfaces\VerifiesService;
use BotMan\Drivers\VK\Exceptions\VkException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

class VkDriver extends HttpDriver implements VerifiesService
{
    const DRIVER_NAME = 'Vk';

    const API_VERSION = '5.103';
    const API_URL = 'https://api.vk.com/method/';

    const CONFIRMATION_EVENT = 'confirmation';
    const MESSAGE_EDIT_EVENT = 'message_edit';
    const MESSAGE_NEW_EVENT = 'message_new';

    const EVENTS = [
        'confirmation',
        'message_edit',
        'message_new',
    ];

    protected $endpoint = 'messages.send';

    /**
     * @var array
     */
    protected $messages = [];

    /** @var Collection */
    protected $queryParameters;


    /**
     * @return bool
     */
    public function matchesRequest()
    {
        return ! is_null($this->payload->get('type')) ||
            ! is_null($this->payload->get('object')) &&
            ! is_null($this->payload->get('group_id')) &&
            count($this->event->get('attachments')) === 0;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        if ($this->payload->get('type') === self::MESSAGE_NEW_EVENT || $this->payload->get('type') === self::MESSAGE_EDIT_EVENT) {
            $this->messages = [
                new IncomingMessage($this->event->get('text'), $this->event->get('from_id'),
                    $this->payload->get('group_id'), $this->event)
            ];
        }

        if (count($this->messages) === 0) {
            $this->messages = [new IncomingMessage('', '', '')];
        }


        return $this->messages;
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return ! empty($this->config->get('token'))
            && ! empty($this->config->get('group_id'))
            && ! empty($this->config->get('verification'));
    }

    /**
     * @param IncomingMessage $matchingMessage
     * @return UserInterface|User
     * @throws VkException
     */
    public function getUser(IncomingMessage $matchingMessage)
    {
        $response = $this->sendRequest('users.get', [
            'user_ids' => $matchingMessage->getSender(),
            'fields' => 'screen_name',
        ], $matchingMessage);

        if (!$response && !$response->isOk())
            throw new VkException('Error get user info.');

        $responseData = json_decode($response->getContent(), true);
        $profileData = Arr::get($responseData, 'response.0');

        $id = Arr::get($profileData, 'id', null);
        $firstName = Arr::get($profileData, 'first_name', null);
        $lastName = Arr::get($profileData, 'last_name', null);
        $userName = Arr::get($profileData, 'screen_name', null);

        if ($userName === null) {
            $userName = strlen(trim($firstName . $lastName)) > 0 ? trim($firstName . $lastName) : $id;
        }

        return new User($matchingMessage->getSender(), $firstName, $lastName, $userName, $profileData);
    }

    /**
     * @param IncomingMessage $matchingMessage
     * @return Response
     */
    public function types(IncomingMessage $matchingMessage)
    {
        $parameters = [
            'type' => 'typing',
            'user_id' => $matchingMessage->getSender(),
            'access_token' => $this->config->get('token'),
            'v' => self::API_VERSION,
        ];

        return $this->http->post($this->buildApiUrl('messages.setActivity'), [], $parameters);
    }

    /**
     * @param IncomingMessage $message
     * @return Answer
     */
    public function getConversationAnswer(IncomingMessage $message)
    {
        $payload = $this->event->get('payload');

        if (null !== $payload) {
            $p = json_decode($this->event->get('payload'), true);

            if (isset($p['button'])) {
                $value = $p['button'];

                return Answer::create($value)
                    ->setInteractiveReply(true)
                    ->setMessage($message)
                    ->setValue($value);
            }
        }

        return Answer::create($message->getText())->setMessage($message);
    }

    public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
    {
        $parameters = array_merge_recursive([
            'user_id' => $matchingMessage->getSender(),
            'access_token' => $this->config->get('token'),
            'v' => self::API_VERSION,
        ], $additionalParameters);

        if ($message instanceof Question) {
            $parameters['message'] = $message->getText();
            $parameters['keyboard'] = json_encode([
                'one_time' => true,
                'buttons' => $this->convertQuestion($message)
            ], JSON_UNESCAPED_UNICODE);
        } elseif ($message instanceof OutgoingMessage) {
            $parameters['message'] = $message->getText();
        } else {
            $parameters['message'] = $message;
        }

        return $parameters;
    }

    /**
     * Convert a Question object into a valid
     * quick reply response object.
     *
     * @param Question $question
     * @return array
     */
    private function convertQuestion(Question $question)
    {
        $replies = Collection::make($question->getButtons())->map(function ($button) {

            return [
                array_merge([
                    'action' => $button['action'],
                    'color' => $button['color'],
                ], $button['additional']),
            ];
        });

        return $replies->toArray();
    }

    /**
     * @param mixed $payload
     * @return Response
     * @throws VkException
     */
    public function sendPayload($payload)
    {
        $response = $this->http->post($this->buildApiUrl($this->endpoint), [], $payload);
        $this->throwExceptionIfResponseNotOk($response);
        return $response;
    }

    /**
     * @param Request $request
     */
    public function buildPayload(Request $request)
    {
        $this->payload = new ParameterBag((array) json_decode($request->getContent(), true));
        $this->event = Collection::make($this->payload->get('object'));
        $this->config = Collection::make($this->config->get('vk'));
        $this->queryParameters = Collection::make($request->query);
        $this->content = $request->getContent();
    }

    /**
     * @param string $endpoint
     * @param array $parameters
     * @param IncomingMessage $matchingMessage
     * @return Response
     */
    public function sendRequest($endpoint, array $parameters, IncomingMessage $matchingMessage)
    {
        $parameters = array_replace_recursive([
            'user_id' => $matchingMessage->getSender(),
            'access_token' => $this->config->get('token'),
            'v' => self::API_VERSION,
        ], $parameters);



        return $this->http->post($this->buildApiUrl($endpoint), [], $parameters);
    }

    /**
     * @param $endpoint
     * @return string
     */
    protected function buildApiUrl($endpoint)
    {
        return self::API_URL . $endpoint;
    }

    /**
     * @param Response $response
     * @throws VkException
     */
    protected function throwExceptionIfResponseNotOk(Response $response)
    {
        if ($response->getStatusCode() !== 200) {
            $responseData = json_decode($response->getContent(), true);
            throw new VkException('Error sending payload: ' . $responseData);
        }

    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function verifyRequest(Request $request)
    {
        if ($this->payload->get('type') === self::CONFIRMATION_EVENT &&
            $this->payload->get('group_id') == $this->config->get('group_id')) {
            return Response::create($this->config->get('verification'))->send();
        }

        return null;
    }
}