<?php

namespace App\Validator;

class CommentValidator extends AbstractValidator
{
    
    private $data;

    private $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'content' => ['required', 'minLen' => 10],
        ];
    }

    public function isValid()
    {
        $this->validate($this->data, $this->rules);
    }
}
