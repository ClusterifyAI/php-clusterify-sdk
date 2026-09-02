<?php

declare(strict_types=1);

namespace Clusterify\Resources;

use Clusterify\DTO\Chatbot\ChatbotResponse;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Http\RequestOptions;

/**
 * Chatbot configuration resource.
 */
final class ChatbotResource extends AbstractResource
{
    /**
     * Retrieve sanitized configuration details for the tenant's chatbot.
     *
     * @param RequestOptions|null $options
     * @return ChatbotResponse
     * @throws ClusterifyException
     */
    public function get(?RequestOptions $options = null): ChatbotResponse
    {
        $response = $this->http->get('/v1/dashboard/chatbot', [], $options);
        return ChatbotResponse::fromArray($response->payload);
    }
}
