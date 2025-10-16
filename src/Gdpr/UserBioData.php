<?php

/*
 * This file is part of fof/user-bio.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\UserBio\Gdpr;

use Flarum\Gdpr\Contracts\DataType;
use Flarum\User\User;

class UserBioData implements DataType
{
    public function __construct(
        protected User $user
    ) {
    }

    public static function dataType(): string
    {
        return 'user-bio';
    }

    public function export(): ?array
    {
        if (empty($this->user->bio)) {
            return null;
        }

        return [
            'bio' => $this->user->bio,
        ];
    }

    public function anonymize(): void
    {
        $this->user->bio = null;
        $this->user->save();
    }

    public function delete(): void
    {
        $this->user->bio = null;
        $this->user->save();
    }

    public static function exportDescription(): string
    {
        return 'User biography text';
    }

    public static function anonymizeDescription(): string
    {
        return 'Remove user biography';
    }

    public static function deleteDescription(): string
    {
        return 'Remove user biography';
    }
}