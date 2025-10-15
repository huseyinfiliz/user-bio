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

use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Gdpr\Contracts\DataProcessor;

class UserBioGdprServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->tag(UserBioGdprProvider::class, DataProcessor::class);
    }
}