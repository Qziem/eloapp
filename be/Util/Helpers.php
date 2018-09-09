<?php
namespace Util;

class Helpers {
    public static function entitiesListToArray($entitiesList) {
        return array_map(
            function($entity) {
                return $entity->toArray();
            },
            $entitiesList
        );
    }
}