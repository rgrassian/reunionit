<?php

namespace App\Form\Model;


use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;


class ChangePassword
{
    /**
     * @SecurityAssert\UserPassword(
     *     message = "Vérifiez votre mot de passe actuel."
     * )
     */
    protected $oldPassword;

    /**
     * @Assert\Length(min=6, minMessage="Votre mot de passe doit faire {{ limit }} caractères minimum.")
     */
    protected $newPassword;

    /**
     * @return mixed
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * @return mixed
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }

    /**
     * @param mixed $oldPassword
     */
    public function setOldPassword($oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    /**
     * @param mixed $newPassword
     */
    public function setNewPassword($newPassword): void
    {
        $this->newPassword = $newPassword;
    }
}