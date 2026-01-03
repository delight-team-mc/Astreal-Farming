<?php

namespace night\libraries\webhook;

class Message implements \JsonSerializable
{

	protected $data = [];

	public static function create(): self
	{
		return new self();
	}

	public function setContent(string $content): void
	{
		$this->data["content"] = $content;
	}

	public function getContent(): ?string
	{
		return $this->data["content"] ?? null;
	}

	public function getUsername(): ?string
	{
		return $this->data["username"] ?? null;
	}

	public function setUsername(string $username): void
	{
		$this->data["username"] = $username;
	}

	public function getAvatarURL(): ?string
	{
		return $this->data["avatar_url"] ?? null;
	}

	public function setAvatarURL(string $avatarURL): void
	{
		$this->data["avatar_url"] = $avatarURL;
	}

	public function addComponent(Component $component): void
	{
		if (!empty(($arr = $component->jsonSerialize()))) $this->data["components"][] = $arr;
	}

	public function addEmbed(Embed $embed): void
	{
		if (!empty(($arr = $embed->jsonSerialize()))) $this->data["embeds"][] = $arr;
	}

	public function setTextToSpeech(bool $ttsEnabled): void
	{
		$this->data["tts"] = $ttsEnabled;
	}

	public function attachFile(string $fileName, ?string $mimeType = null, ?string $postedFileName = null): Message
	{
		$this->data['file'] = curl_file_create($fileName, $mimeType, $postedFileName);
		return $this;
	}

	public function hasFile(): bool
	{
		return isset($this->data['file']);
	}

	public function jsonSerialize(): array
	{
		return $this->data;
	}
}
