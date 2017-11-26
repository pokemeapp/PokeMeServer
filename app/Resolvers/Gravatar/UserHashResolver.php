<?php

namespace App\Resolvers\Gravatar;

class UserHashResolver
{
    protected $email;

    /**
     * UserHashResolver constructor.
     * @param $email
     */
    public function __construct($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUserHash()
    {
        return md5(strtolower(trim($this->email)));
    }
}
