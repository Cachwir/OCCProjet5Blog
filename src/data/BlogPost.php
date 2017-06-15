<?php

namespace src\data;

use lib\ORM;

/**
 * Class BlogPost
 */
class BlogPost extends ORM {

	public static $table_name = "blog_posts";
	public static $fields = [
		'id'                        => 'i',
        'author'                    => 's',
        'publication_date'          => 'i',
        'last_modification_date'    => 'i',
        'title'                     => 's',
        'introduction'              => 's',
        'content'                   => 's',
	];

    public static $table_primary_key = "id";

    public static $table_objects = [];

	public static $computed_fields = [];

	public static function findFromLast()
    {
        return self::findByWhere("1 ORDER BY id DESC");
    }
}