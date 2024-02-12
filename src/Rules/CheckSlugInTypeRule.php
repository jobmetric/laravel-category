<?php

namespace JobMetric\Category\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use JobMetric\Category\Models\Category;

class CheckSlugInTypeRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string|null $type,
        private readonly int|null $category_id = null
    )
    {
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(is_null($this->type)) {
            $fail(__('category::base.validation.type_required'));

            return;
        }

        if($this->category_id) {
            if (Category::query()->where('slug', $value)->where('type', $this->type)->where('id', '!=', $this->category_id)->exists()) {
                $fail(__('category::base.validation.slug_in_type', ['type' => $this->type]));
            }

            return;
        }

        if (Category::query()->where('slug', $value)->where('type', $this->type)->exists()) {
            $fail(__('category::base.validation.slug_in_type', ['type' => $this->type]));
        }
    }
}
