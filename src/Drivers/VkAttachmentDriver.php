<?php


namespace VkBotMan\Drivers;


use BotMan\BotMan\Messages\Attachments\File;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class VkAttachmentDriver extends VkDriver
{
    public function matchesRequest()
    {
        $attachments = $this->event->get('attachments');

        if (count($attachments) < 1) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function hasMatchingEvent()
    {
        return false;
    }

    /**
     * Retrieve the chat message.
     *
     * @return array
     */
    public function getMessages()
    {
        if (empty($this->messages)) {
            $this->loadMessages();
        }

        return $this->messages;
    }

    public function loadMessages()
    {
        $message = null;

        if (count($this->getImages()) > 0) {
            $message = new IncomingMessage(Image::PATTERN,
                $this->event->get('from_id'),
                $this->payload->get('group_id'),
                $this->event
            );
            $message->setImages($this->getImages());
        }
        elseif (count($this->getAttachments()) > 0) {
            $message = new IncomingMessage(File::PATTERN,
                $this->event->get('from_id'),
                $this->payload->get('group_id'),
                $this->event
            );
            $message->setFiles($this->getAttachments());
        }

        if (null !== $message) {
            $this->messages = [$message];
        }
    }

    private function getAttachments()
    {
        $attachments = $this->event->get('attachments');
        $output = [];

        foreach ($attachments as $attachment) {
            if (isset($attachment['photo'])) {
                continue;
            }

            $file = new File('http://', $attachment);
            $output[] = $file;
        }

        return $output;
    }

    /**
     * Retrieve a image from an incoming message.
     * @return array A download for the image file.
     */
    private function getImages()
    {
        $photos = $this->event->get('attachments');
        $images = [];

        foreach ($photos as $photo) {

            if (!isset($photo['photo'])) {
                continue;
            }

            $firstPhoto = $photo;
            $sizes = $firstPhoto['photo']['sizes'];

            $biggest = null;
            $sizeb = 0;

            foreach ($sizes as $size) {
                if ($size['height'] > $sizeb) {
                    $biggest = $size;
                    $sizeb = $size['height'];
                }
            }

            $image = new Image($biggest['url'], $biggest);
            $images[] = $image;
        }

        return $images;
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return false;
    }
}