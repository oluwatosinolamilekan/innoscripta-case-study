<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class DateRange implements Rule
{
    /**
     * The minimum date allowed.
     *
     * @var string|null
     */
    protected $minDate;

    /**
     * The maximum date allowed.
     *
     * @var string|null
     */
    protected $maxDate;

    /**
     * Create a new rule instance.
     *
     * @param string|null $minDate
     * @param string|null $maxDate
     * @return void
     */
    public function __construct($minDate = null, $maxDate = null)
    {
        $this->minDate = $minDate;
        $this->maxDate = $maxDate ?: now()->format('Y-m-d');
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
        $date = Carbon::parse($value);
        
        if ($this->minDate && $date->isBefore(Carbon::parse($this->minDate))) {
            return false;
        }
        
        if ($this->maxDate && $date->isAfter(Carbon::parse($this->maxDate))) {
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
        if ($this->minDate && $this->maxDate) {
            return "The :attribute must be between {$this->minDate} and {$this->maxDate}.";
        } elseif ($this->minDate) {
            return "The :attribute must be after or equal to {$this->minDate}.";
        } elseif ($this->maxDate) {
            return "The :attribute must be before or equal to {$this->maxDate}.";
        }
        
        return "The :attribute is not within a valid date range.";
    }
}
