<?php

namespace wcf\system\form\builder\field\wysiwyg;

use wcf\system\form\builder\field\TI18nFormField;

class I18nWysiwygFormField extends WysiwygFormField
{
    use TI18nFormField;

    protected $templateName = '__i18nWysiwygFormField';
}
