<?php

namespace App\Validator;

class ContactValidator extends AbstractValidator
{
    private $data;

    private $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'email' => ['required', 'minLen' => 3,'email'],
            'message' => ['required', 'minLen' => 10],
            'firstname' => ['required', 'minLen' => 3],
            'lastname' => ['required', 'minLen' => 3],
        ];
    }

    public function isValid()
    {
        $this->validate($this->data, $this->rules);
    }
}
