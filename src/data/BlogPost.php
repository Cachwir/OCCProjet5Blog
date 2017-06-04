<?php

/**
 * Class BlogPost
 */
class BlogPost extends ORM {

	public static $tableName = "blog_posts";
	public static $fields = [
		'id'                     => 'i',
	];

	public static $computedFields = [];
}