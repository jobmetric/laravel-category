<?php

namespace JobMetric\Category\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use JobMetric\Category\Models\Category;

class CategoryExistRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $type
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
        if ($value == 0) {
            return;
        }

        if (!Category::query()->where('id', $value)->where('type', $this->type)->exists()) {
            $fail(__('category::base.validation.category_exist', ['type' => $this->type]));
        }
    }
}
