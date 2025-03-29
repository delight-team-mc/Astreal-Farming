<?php

declare(strict_types = 1);

namespace astreal\libraries\form\simple;

use astreal\libraries\form\simple\ModalForm;
use astreal\libraries\form\simple\CustomForm;
use astreal\libraries\form\simple\SimpleForm;
use astreal\libraries\form\simple\TemplateForm;

trait FormAPI {

  public static function createTemplateForm(string $title = '', callable $function = null): TemplateForm{
    return new TemplateForm($title, $function);
  }
  
  public static function createCustomForm(callable $function = null): CustomForm {
    return new CustomForm($function);
  }
  
  public static function createSimpleForm(callable $function = null): SimpleForm {
    return new SimpleForm($function);
  }
  
  public static function createModalForm(callable $function = null): ModalForm {
    return new ModalForm($function);
  }
}