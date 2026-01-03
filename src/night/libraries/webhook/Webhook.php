<?php

namespace night\libraries\webhook;

use pocketmine\Server;

class Webhook
{

	protected $url;

	public static function create(string $url): self
	{
		return new self($url);
	}

	public function __construct(string $url)
	{
		$this->url = $url;
	}

	public function getURL(): string
	{
		return $this->url;
	}

	public function isValid(): bool
	{
		return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
	}

	public function send(Message $message): void
	{
		$response = (new SendRequest($this, $message))->execute();
		if (!in_array($response[1], [200, 204])) {
			Server::getInstance()->getLogger()->error("[DiscordWebhookAPI] Got error ({$response[1]}): " . $response[0]);
		}
	}
}
