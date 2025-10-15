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
use ZipArchive;

class UserBioData implements DataType
{
    public function export(User $user, ZipArchive $zip): ?array
    {
        if (empty($user->bio)) {
            return null;
        }

        return [
            'bio' => $user->bio,
        ];
    }

    public function anonymize(User $user): void
    {
        $user->bio = null;
        $user->save();
    }

    public function delete(User $user): void
    {
        $user->bio = null;
        $user->save();
    }

    public static function exportDescription(): string
    {
        return 'User biography text';
    }
}