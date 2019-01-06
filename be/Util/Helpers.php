<?php

namespace Util;

class Helpers
{
    public static function entitiesListToArray(array $entitiesList): array
    {
        return array_map(
            function ($entity) {
                return $entity->toArray();
            },
            $entitiesList
        );
    }
}