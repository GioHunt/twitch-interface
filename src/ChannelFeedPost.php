<?php

namespace Twitch;

use Twitch\BaseMethod;
use Twitch\Traits\CallStatically;
use Twitch\Exceptions\ChannelFeedPostException;

class ChannelFeedPost extends BaseMethod
{
    use CallStatically;
    
    function __construct(array $params)
    {
        if (empty($params['channel']) || empty($params['post_id'])) {
            throw new ChannelFeedPostException("We require both the channel and post_id to be passed to ChannelFeedPost as an array.");
        }

        $this->setEndpoint("feed/{$params['channel']}/posts/{$params['post_id']}");

        $curl = Twitch::Api($this->endpoint())->get();
        $this->setData($curl->data());
    }

    public function delete()
    {
        if (Twitch::$scope->isAuthorized('channel_feed_edit') === false) {
            throw new TwitchScopeException("You do not have sufficient scope priviledges to run this command. Make sure you're authorized for `channel_feed_edit`.", 401);
        }

        return Twitch::api($this->endpoint())->delete();
    }

    /**
     * React to a post
     *
     * This posts a reaction to the post on behalf of the access token holder.
     * Emote ID 25 is Kappa.
     */
    public function react($emote_id)
    {
        if (Twitch::$scope->isAuthorized('channel_feed_edit') === false) {
            throw new TwitchScopeException("You do not have sufficient scope priviledges to run this command. Make sure you're authorized for `channel_feed_edit`.", 401);
        }

        if (!is_string($emote_id) && !is_numeric($emote_id)) {
            throw new InvalidArgumentException("Emote ID must be either a string or a number.");
        }

        $response = Twitch::api($this->endpoint() . "/reactions")->post([
            'emote_id' => (string) $emote_id
        ]);
        
        return $this;
    }
    
    /**
     * This undoes the reaction from the method above.
     */
    public function unreact($emote_id)
    {
        if (Twitch::$scope->isAuthorized('channel_feed_edit') === false) {
            throw new TwitchScopeException("You do not have sufficient scope priviledges to run this command. Make sure you're authorized for `channel_feed_edit`.", 401);
        }

        if (!is_string($emote_id) && !is_numeric($emote_id)) {
            throw new InvalidArgumentException("Emote ID must be either a string or a number.");
        }

        $response = Twitch::api($this->endpoint() . "/reactions")->delete([
            'emote_id' => (string) $emote_id
        ]);
        
        return $this;
    }
}