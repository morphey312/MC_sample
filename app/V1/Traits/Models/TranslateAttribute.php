<?php

namespace App\V1\Traits\Models;

use App\V1\Contracts\Multilingual;
use Auth;

trait TranslateAttribute
{
    public function i18n($attribute)
    {
        $user = Auth::user();

        if ($user instanceof Multilingual) {
            $suffix = $user->getLocaleSuffix();
            if ($suffix !== null) {
                $i18nAttribute = $attribute . '_' . $suffix;
                if (array_key_exists($i18nAttribute, $this->attributes)) {
                    $localized = $this->getAttribute($i18nAttribute);
                    if (!empty($localized)) {
                        return $localized;
                    }
                }
            }
        }

        return $this->getAttribute($attribute);
    }
}