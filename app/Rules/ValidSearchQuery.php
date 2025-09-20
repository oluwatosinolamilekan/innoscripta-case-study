<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidSearchQuery implements Rule
{
    /**
     * The minimum length of the search query.
     *
     * @var int
     */
    protected $minLength;

    /**
     * Create a new rule instance.
     *
     * @param int $minLength
     * @return void
     */
    public function __construct($minLength = 2)
    {
        $this->minLength = $minLength;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check if the search query is at least the minimum length
        if (strlen($value) < $this->minLength) {
            return false;
        }
        
        // Check if the search query contains only valid characters
        if (!preg_match('/^[\p{L}\p{N}\s\-_.,\'":;!?()[\]{}@#$%^&*+=\/\\|<>~`]+$/u', $value)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The :attribute must be at least {$this->minLength} characters long and contain only valid characters.";
    }
}
