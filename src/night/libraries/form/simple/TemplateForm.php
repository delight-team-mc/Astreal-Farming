<?php

declare(strict_types = 1);

namespace night\libraries\form\simple;

use pocketmine\player\Player;

class TemplateForm extends Form {
    private array $closures = [];
    private array $labelMap = [];

    public function __construct(string $title = '', ?callable $callble = null){
        parent::__construct($callble);
        $this->data["type"] = "custom_form";
        $this->data["title"] = $title;
        $this->data["content"] = [];
    }

    public function processData(&$data) : void {
        if(is_array($data)) {
            $new = [];
            foreach ($data as $i => $v) {
                $new[$this->labelMap[$i]] = $v;
            }
            $data = $new;
        }
    }

    public function handleResponse(Player $player, $data): void{
        if(!is_null($data)){
            foreach((array)$data as $i => $v){
                $closure = $this->closures[$i] ?? null;
                if(!is_null($closure))$closure($player, $v);
            }
        }
        $callable = $this->getCallable();
        if($callable !== null) {
            $callable($player, $data);
        }
    }

    public function setTitle(string $title) : void {
        $this->data["title"] = $title;
    }

    public function getTitle() : string {
        return $this->data["title"];
    }

    public function addLabel(string $text) : void {
        $this->addContent(["type" => "label", "text" => $text]);
    }

    public function addToggle(string $text, bool $default = null, ?callable $closure = null) : void {
        $content = ["type" => "toggle", "text" => $text];
        if($default !== null) {
            $content["default"] = $default;
        }
        $this->addContent($content, $closure);
    }

    public function addSlider(string $text, int $min, int $max, int $step = -1, int $default = -1, ?callable $closure = null) : void {
        $content = ["type" => "slider", "text" => $text, "min" => $min, "max" => $max];
        if($step !== -1) {
            $content["step"] = $step;
        }
        if($default !== -1) {
            $content["default"] = $default;
        }
        $this->addContent($content, $closure);
    }

    public function addStepSlider(string $text, array $steps, int $defaultIndex = -1, ?callable $closure = null) : void {
        $content = ["type" => "step_slider", "text" => $text, "steps" => $steps];
        if($defaultIndex !== -1) {
            $content["default"] = $defaultIndex;
        }
        $this->addContent($content, $closure);
    }

    public function addDropdown(string $text, array $options, ?int $default = null, ?callable $closure = null) : void {
        $this->addContent(["type" => "dropdown", "text" => $text, "options" => $options, "default" => $default], $closure);
    }

    public function addInput(string $text, string $placeholder, ?string $default = null, ?callable $closure = null) : void {
        $this->addContent(["type" => "input", "text" => $text, "placeholder" => $placeholder, "default" => $default], $closure);
    }

    private function addContent(array $content, ?callable $closure = null) : void {
        $this->data["content"][] = $content;
        $this->closures[] = $closure;
    }
}

