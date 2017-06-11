<?php

namespace src\data;

use lib\ORM;

/**
 * Class BlogPost
 */
class BlogPost extends ORM {

	public static $tableName = "blog_posts";
	public static $fields = [
		'id'                        => 'i',
        'author'                    => 's',
        'publication_date'          => 'i',
        'last_modification_date'    => 'i',
        'title'                     => 's',
        'introduction'              => 's',
        'content'                   => 's',
	];

	public static $computedFields = [];

	public static function findFromLast()
    {
        return self::findByWhere("1 ORDER BY id DESC");
    }
}