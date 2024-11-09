<?php

namespace JobMetric\Taxonomy\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use JobMetric\Taxonomy\Models\Taxonomy;

class TaxonomyExistRule implements ValidationRule
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

        if (!Taxonomy::query()->where('id', $value)->where('type', $this->type)->exists()) {
            $fail(__('taxonomy::base.validation.taxonomy_exist', ['type' => $this->type]));
        }
    }
}
