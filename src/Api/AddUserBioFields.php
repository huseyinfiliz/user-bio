<?php

/*
 * This file is part of fof/user-bio.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\UserBio\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use FoF\UserBio\Formatter\UserBioFormatter;

class AddUserBioFields
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected UserBioFormatter $formatter
    ) {
    }

    public function __invoke(): array
    {
        return [
            // Bio field - writable, visible based on permission
            Schema\Str::make('bio')
                ->get(fn (User $user, Context $context) => $this->getBio($user, $context))
                ->set(function (User $user, string $value, Context $context) {
                    // Yetki kontrolü SaveUserBio listener'ında yapılıyor
                    // Burada sadece değeri set ediyoruz
                    return $value;
                })
                ->visible(fn (User $user, Context $context) => $context->getActor()->can('viewBio', $user)),

            // BioHtml field - read only, rendered HTML version
            Schema\Str::make('bioHtml')
                ->get(fn (User $user, Context $context) => $this->getBioHtml($user, $context))
                ->visible(fn (User $user, Context $context) => $context->getActor()->can('viewBio', $user)),

            // CanViewBio - permission flag
            Schema\Boolean::make('canViewBio')
                ->get(fn (User $user, Context $context) => $context->getActor()->can('viewBio', $user)),

            // CanEditBio - permission flag
            Schema\Boolean::make('canEditBio')
                ->get(fn (User $user, Context $context) => $context->getActor()->can('editBio', $user)),
        ];
    }

    protected function getBio(User $user, Context $context): ?string
    {
        $actor = $context->getActor();
        
        if (!$actor->can('viewBio', $user)) {
            return null;
        }

        $bio = $user->bio ?? '';
        $isXML = str_starts_with($bio, '<') && str_ends_with($bio, '>');
        $allowFormatting = $this->settings->get('fof-user-bio.allowFormatting', false);
        $canEdit = $actor->can('editBio', $user);

        if ($isXML) {
            // Show unparsed bio if formatting disabled or user can edit
            if (!$allowFormatting || $canEdit) {
                return $this->formatter->unparse($bio);
            }
            return null;
        }

        return $bio;
    }

    protected function getBioHtml(User $user, Context $context): ?string
    {
        $actor = $context->getActor();
        
        if (!$actor->can('viewBio', $user)) {
            return null;
        }

        $bio = $user->bio ?? '';
        $isXML = str_starts_with($bio, '<') && str_ends_with($bio, '>');
        $allowFormatting = $this->settings->get('fof-user-bio.allowFormatting', false);

        if ($isXML && $allowFormatting) {
            return $this->formatter->render($bio);
        }

        return null;
    }
}