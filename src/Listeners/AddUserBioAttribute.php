<?php

/*
 * This file is part of fof/user-bio.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\UserBio\Listeners;

use Flarum\Api\Event\Serializing;
use Flarum\Api\Serializer\UserSerializer;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use FoF\UserBio\Formatter\UserBioFormatter;

class AddUserBioAttribute
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected UserBioFormatter $formatter
    ) {
    }

    public function handle(Serializing $event): void
    {

        // Only handle UserSerializer events
        if (!($event->serializer instanceof UserSerializer)) {
            return;
        }

        /** @var User $user */
        $user = $event->model;
        $attributes = &$event->attributes;
        $actor = $event->serializer->getActor();

        $bio = $user->bio ?? '';
        $isXML = str_starts_with($bio, '<') && str_ends_with($bio, '>');
        $allowFormatting = $this->settings->get('fof-user-bio.allowFormatting', false);

        if ($actor->can('viewBio', $user)) {
            $canEdit = $actor->can('editBio', $user);

            if ($isXML) {
                // If formatting is enabled, render the bio HTML. Otherwise pass the unparsed formatting.
                $attributes['bioHtml'] = $allowFormatting ? $this->formatter->render($bio) : null;

                if (!$allowFormatting || $canEdit) {
                    $attributes['bio'] = $this->formatter->unparse($bio);
                }
            } else {
                $attributes['bio'] = $bio;
            }

            $attributes['canViewBio'] = true;
            $attributes['canEditBio'] = $canEdit;
        }
    }
}