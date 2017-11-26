<?php

namespace App\Resolvers\Gravatar;

class GravatarImageResolver
{
    protected $email;
    protected $url;
    protected $image;

    /**
     * GravatarImageResolver constructor.
     * @param $email
     */
    public function __construct($email)
    {
        $this->email = $email;
    }

    public function getImageUrl()
    {
        $hash = (new UserHashResolver($this->email))->getUserHash();
        return "https://www.gravatar.com/avatar/" . $hash . "?r=g&s=300";
    }

}
