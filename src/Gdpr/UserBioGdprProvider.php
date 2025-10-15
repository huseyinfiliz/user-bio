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

use Flarum\Gdpr\Contracts\DataProcessor;
use Flarum\Gdpr\Data;
use Flarum\User\User;
use Illuminate\Support\Arr;

class UserBioGdprProvider implements DataProcessor
{
    public function export(User $user): ?Data
    {
        if (empty($user->bio)) {
            return null;
        }

        return new Data(
            'user-bio',
            'User Bio',
            [
                'bio' => $user->bio,
            ]
        );
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

    public function exportDescription(): string
    {
        return 'User biography text';
    }
}