<?php

namespace App\Security;

use App\Entity\Page;
use App\Entity\UserCustomer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class PageVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], false)) {
            return false;
        }

        if (!$subject instanceof Page) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        if (!$user instanceof UserCustomer) {
            return false;
        }

        /** @var Page $subject */
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        throw new \LogicException('Something went wrong in Page voting!');
    }

    private function canView(Page $page, User $user): bool
    {
        if($page->isDeleted()) {
            return false;
        }

        // if they can edit, they can view
        if ($this->canEdit($page, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Page $page, User $user): bool
    {
        if($page->isDeleted()) {
            return false;
        }

        return $user === $page->getUser();
    }

    private function canDelete(Page $page, User $user): bool
    {
        return $user === $page->getUser();
    }
}
