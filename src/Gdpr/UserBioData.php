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
    public static function dataType(): string
    {
        return 'user-bio';
    }

    public function export(?User $user = null, ?ZipArchive $zip = null): ?array
    {
        if (!$user || empty($user->bio)) {
            return null;
        }

        return [
            'bio' => $user->bio,
        ];
    }

    public function anonymize(?User $user = null): void
    {
        if (!$user) {
            return;
        }

        $user->bio = null;
        $user->save();
    }

    public function delete(?User $user = null): void
    {
        if (!$user) {
            return;
        }

        $user->bio = null;
        $user->save();
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