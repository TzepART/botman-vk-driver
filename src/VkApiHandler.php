<?php


namespace VkBotMan;

use GuzzleHttp\Client;

/**
 * Class VkApiHandler
 * @package VkBotMan
 */
class VkApiHandler
{
    /**
     * @param $groupId
     * @param $memberId
     * @return bool
     */
    public function isGroupMember($groupId, $memberId)
    {
        $isMember = false;

        $client = new Client();

        //TODO parms in config
        try {
            $response = $client->get('https://api.vk.com/method/groups.isMember', [
                'query' => [
                    'group_id' => $groupId,
                    'user_id' => $memberId,
                    'access_token' => getenv('VK_GROUP_ACCESS_TOKEN'),
                    'v' => '5.85'
                ]
            ]);

            $output = $response->getBody()->getContents();
            $output = json_decode($output, true);

            if (isset($output['response']) && (int)$output['response'] === 1) {
                $isMember = true;
            }

        } catch (\Exception $e) {
            $isMember = false;
        }

        return $isMember;
    }
}