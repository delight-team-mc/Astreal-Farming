<?php

namespace night\libraries\webhook;

class Embed implements \JsonSerializable
{
	protected array $data = [];

	public static function create(): self
	{
		return new self();
	}

	public static function paserField(mixed $value, bool $inline = false): array
	{
		return ['value' => "{$value}", 'inline' => $inline];
	}

	public function setAuthor(string $name, ?string $url = null, ?string $iconURL = null): void
	{
		if (!isset($this->data["author"])) {
			$this->data["author"] = [];
		}
		$this->data["author"]["name"] = $name;
		if ($url !== null) {
			$this->data["author"]["url"] = $url;
		}
		if ($iconURL !== null) {
			$this->data["author"]["icon_url"] = $iconURL;
		}
	}

	public function setTitle(string $title): void
	{
		$this->data["title"] = $title;
	}

	public function setDescription(string $description): void
	{
		$this->data["description"] = $description;
	}

	public function setColor(int $color): void
	{
		$this->data["color"] = $color;
	}

	public function addField(string $name, string $value, bool $inline = false): void
	{
		if (!isset($this->data["fields"])) $this->data["fields"] = [];
		$this->data["fields"][] = ['name' => $name, 'value' => $value, 'inline' => $inline];
	}

	public function addFields(array $fields): void
	{
		foreach ($fields as $name => $v) {
			if (!isset($v['inline'])) $v['inline'] = false;
			if (isset($v['inline']) && !is_bool($v['inline'])) $v['inline'] = false;
			if (!isset($v['value'])) return;
			$this->addField($name, $v['value'], $v['inline']);
		}
	}

	public function setThumbnail(string $url): void
	{
		if (!isset($this->data["thumbnail"])) {
			$this->data["thumbnail"] = [];
		}
		$this->data["thumbnail"]["url"] = $url;
	}

	public function setImage(string $url): void
	{
		if (!isset($this->data["image"])) {
			$this->data["image"] = [];
		}
		$this->data["image"]["url"] = $url;
	}

	public function setFooter(string $text, string $iconURL = null): void
	{
		if (!isset($this->data["footer"])) {
			$this->data["footer"] = [];
		}
		$this->data["footer"]["text"] = $text;
		if ($iconURL !== null) {
			$this->data["footer"]["icon_url"] = $iconURL;
		}
	}

	public function setTimestamp(\DateTime $timestamp): void
	{
		$timestamp->setTimezone(new \DateTimeZone("UTC"));
		$this->data["timestamp"] = $timestamp->format("Y-m-d\TH:i:s.v\Z");
	}

	public function jsonSerialize(): array
	{
		return $this->data;
	}
}
