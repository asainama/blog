<?php

namespace App\Validator;

class UserValidator extends AbstractValidator
{
    
    private $data;

    private $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'email' => ['required', 'minLen' => 3,'email'],
            'password' => ['required', 'minLen' => 4],
        ];
    }

    public function isValid()
    {
        $this->validate($this->data, $this->rules);
    }
}
