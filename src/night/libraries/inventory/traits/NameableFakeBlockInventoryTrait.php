<?php

namespace night\libraries\inventory\traits;

trait NameableFakeBlockInventoryTrait{
    private string $custom_name='';

    public function getName():string{
        return $this->hasName()?$this->custom_name:$this->getDefaultName();
    }
    public function setName(string $name):void{
        $this->custom_name=$name;
    }
    public function hasName():bool{
        return $this->custom_name !== '';
    }
}