<?php

/*
 * This file is part of fof/user-bio.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\UserBio;

use Flarum\Api\Context;
use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Extend as Flarum;
use Flarum\Settings\Event\Saved;
use Flarum\User\Event\Saving;
use Flarum\User\User;

return [
    (new Flarum\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/resources/less/forum.less'),

    (new Flarum\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Flarum\Locales(__DIR__ . '/resources/locale'),

    (new Flarum\Model(User::class))
        ->cast('bio', 'string'),

    (new Flarum\Event())
        ->listen(Saving::class, Listeners\SaveUserBio::class)
        ->listen(Saved::class, Listeners\ClearFormatterCache::class),

    // Flarum 2.x JSON:API - declare ALL bio-related fields via ApiResource
    (new Flarum\ApiResource(Resource\UserResource::class))
        ->fields(function () {
            /** @var \Flarum\Settings\SettingsRepositoryInterface $settings */
            $settings = resolve(\Flarum\Settings\SettingsRepositoryInterface::class);
            /** @var \FoF\UserBio\Formatter\UserBioFormatter $formatter */
            $formatter = resolve(\FoF\UserBio\Formatter\UserBioFormatter::class);
            
            return [
                // Bio field - writable, visible based on permission
                Schema\Str::make('bio')
                    ->get(function (User $user, Context $context) use ($settings, $formatter) {
                        $actor = $context->getActor();
                        if (!$actor->can('viewBio', $user)) {
                            return null;
                        }
                        
                        $bio = $user->bio ?? '';
                        $isXML = str_starts_with($bio, '<') && str_ends_with($bio, '>');
                        $allowFormatting = $settings->get('fof-user-bio.allowFormatting', false);
                        $canEdit = $actor->can('editBio', $user);
                        
                        if ($isXML) {
                            // Show unparsed bio if formatting disabled or user can edit
                            if (!$allowFormatting || $canEdit) {
                                return $formatter->unparse($bio);
                            }
                            return null;
                        }
                        
                        return $bio;
                    })
                    ->writable()
                    ->visible(fn (User $user, Context $context) => $context->getActor()->can('viewBio', $user)),
                    
                // BioHtml field - read only, rendered HTML version
                Schema\Str::make('bioHtml')
                    ->get(function (User $user, Context $context) use ($settings, $formatter) {
                        $actor = $context->getActor();
                        if (!$actor->can('viewBio', $user)) {
                            return null;
                        }
                        
                        $bio = $user->bio ?? '';
                        $isXML = str_starts_with($bio, '<') && str_ends_with($bio, '>');
                        $allowFormatting = $settings->get('fof-user-bio.allowFormatting', false);
                        
                        if ($isXML && $allowFormatting) {
                            return $formatter->render($bio);
                        }
                        
                        return null;
                    })
                    ->visible(fn (User $user, Context $context) => $context->getActor()->can('viewBio', $user)),
                    
                // CanViewBio - permission flag
                Schema\Boolean::make('canViewBio')
                    ->get(fn (User $user, Context $context) => $context->getActor()->can('viewBio', $user)),
                    
                // CanEditBio - permission flag
                Schema\Boolean::make('canEditBio')
                    ->get(fn (User $user, Context $context) => $context->getActor()->can('editBio', $user)),
            ];
        }),

    (new Flarum\Policy())
        ->modelPolicy(User::class, Access\UserPolicy::class),

    (new Flarum\Settings())
        ->serializeToForum('fof-user-bio.maxLength', 'fof-user-bio.maxLength', 'intVal')
        ->serializeToForum('fof-user-bio.maxLines', 'fof-user-bio.maxLines', 'intVal')
        ->default('fof-user-bio.maxLength', 200)
        ->default('fof-user-bio.maxLines', 5),

    (new Flarum\ServiceProvider())
        ->register(Formatter\FormatterServiceProvider::class),
];