<?php

namespace App\Validator;

abstract class AbstractValidator
{
    private $errors = [];

    protected function validate($src, $rules = [])
    {
        if ($src !== null) {
            foreach ($src as $item => $item_value) {
                if (key_exists($item, $rules)) {
                    foreach ($rules[$item] as $rule => $rule_value) {
                        if (is_int($rule)) {
                            $rule = $rule_value;
                        }
                        switch ($rule) {
                            case 'required':
                                if (empty($item_value) && $rule_value) {
                                    $this->addError($item, ucwords($item) . ' est requis');
                                }
                                break;

                            case 'minLen':
                                if (strlen($item_value) < $rule_value) {
                                    $this->addError($item, ucwords($item)
                                    . ' devrait posséder une taille de '
                                    . $rule_value
                                    . ' caractères au minimum');
                                }
                                break;
    
                            case 'maxLen':
                                if (strlen($item_value) > $rule_value) {
                                    $this->addError($item, ucwords($item)
                                    . ' devrait posséder une taille de'
                                    . $rule_value
                                    . ' caractères au maximum');
                                }
                                break;
    
                            case 'numeric':
                                if (!ctype_digit($item_value) && $rule_value) {
                                    $this->addError($item, ucwords($item)
                                    . ' devrait être un numérique');
                                }
                                break;
                            case 'alpha':
                                if (!ctype_alpha($item_value) && $rule_value) {
                                    $this->addError($item, ucwords($item)
                                    . ' doit être des caractères alphabétiques');
                                }
                                break;
                            case 'email':
                                if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $item_value)) {
                                    $this->addError($item, ucwords($item)
                                    . ' n\'est pas un email valide');
                                }
                                break;
                            case 'date':
                                if (!$this->validateDate($item_value)) {
                                    $this->addError($item, ucwords($item)
                                    . ' n\'est pas une date valide');
                                }
                                break;
                            case 'bool':
                                $item_value = boolval($item_value);
                                if (!is_bool($item_value)) {
                                    $this->addError($item, ucwords($item)
                                    . ' n\'est pas une valeur booléenne valide');
                                }
                                break;
                        }
                    }
                }
            }
        }
    }

    private function addError($item, $error)
    {
        $this->errors[$item][] = "Le champs " . $error;
    }

    private function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    public function error()
    {
        if (empty($this->errors)) {
            return false;
        }
        return $this->errors;
    }
}
