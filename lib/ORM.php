<?php

/**
 * Class ORM
 *
 * If you want to manage entities, create one and make it extend this ORM.
 * Then, override its parameters and fill them with this entity's parameters, table's name, etc.
 *
 * You can create your own methods to have more specific db researches.
 *
 * If you have any doubt, you can look at the existing entities and compare it to the entity you wish to create.
 */

namespace lib;

abstract class ORM implements \ArrayAccess {

    /**
     * The table name
     *
     * @var string
     */
	public static $table_name;

    /**
     * An array containing the table fields (keys) associated to
     * their types (values). Types are the ones used in cast_array_types()
     *
     * Please, refrain from using an "object" field name as it would cause bugs
     *
     * @var array
     */
	public static $fields = [];

    /**
     * The primary key
     *
     * @var string
     */
    public static $table_primary_key = "id";

    /**
     * An array containing the table fields (keys) that are foreign
     * keys of instantiable objects, associated with the objects
     * classes (values).
     *
     * @var array
     */
    public static $table_objects = [];

    /**
     * An array containing unsaved fields (keys) associated to
     * their types (values). Types are the ones used in cast_array_types()
     *
     * @var array
     */
	public static $computed_fields = [];

    /**
     * @var array $data Where the $fields and $computed_fields data is stored
     */
	protected $data = [];
    /**
     * @var array $db_objects An array of field_name => entity used with getObject and setObject
     */
    protected $db_objects = [];


	public static function getFields() {
		return static::$fields + static::$computed_fields;
	}

	public static function getDbFields() {
		return static::$fields;
	}

	public static function getComputedFields() {
		return static::$computed_fields;
	}

	public static function findById($id) {
		return static::findOneByWhere(self::$table_primary_key . " = :id", [':id' => $id]);
	}

	public static function findByIds($ids) {
		return static::findByWhere(self::$table_primary_key . " in (:ids)", [':ids' => $ids]);
	}

	public static function findByWhere($where, $params = []) {
		$rows = PDOWrapper::get()->fetchAll("select * from ".static::$table_name." where $where", $params);

		$Entities = [];

		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$Entities[] = static::createObjectFromRow($row);
			}
		}

		return $Entities;
	}

	public static function findOneByWhere($where, $params = []) {
		$row = PDOWrapper::get()->fetch("select * from ".static::$table_name." where $where limit 1", $params);
		return static::createObjectFromRow($row);
	}

	public static function findAll() {
		return static::findByWhere("1");
	}

    protected static function createObjectFromRow($row) {
        if (is_array($row)) {
            $ClassName = get_called_class();
            $Entity = new $ClassName();

            foreach ($row as $field => $value) {
                $Entity->set($field, $value);
            }

            return $Entity;
        } else {
            return null;
        }
    }

    /**
     * Return the specified field's type
     *
     * @param   string  $field
     * @return  string
     */
	public static function getFieldType($field) {
		if (!array_key_exists($field, static::getFields())) {
			return null;
		}

		return static::getFields()[$field];
	}

    /**
     * Return the value of the specified field
     *
     * @param   string  $field
     * @param   string  $value
     * @param   bool    $allow_setter
     * @return  ORM
     */
	public function set($field, $value, $allow_setter = true) {
		$method = camelize("set_$field");
		if ($allow_setter && method_exists($this, $method)) {
			return call_user_func([$this, $method], $value);
		}

		if (!array_key_exists($field, static::getFields())) {
			throw new \Exception("Invalid field: $field");
		}

		$this->data[$field] = cast($value, static::getFieldType($field));

		return $this;
	}

    /**
     * Return the value of the specified field
     *
     * @param   string  $field
     * @param   mixed   $default    The returned value if the field wasn't set
     * @param   bool    $allow_getter
     * @return  mixed
     */
	public function get($field, $default = null, $allow_getter = true) {
		$method = camelize("get_$field");
		if ($allow_getter && method_exists($this, $method)) {
			return call_user_func([$this, $method]);
		}

		if (!array_key_exists($field, static::getFields())) {
			throw new \Exception("Invalid field: $field");
		}

		return isset($this->data[$field]) ? $this->data[$field] : $default;
	}

    /**
     * Return the value of the primary key of the object (aka ID)
     *
     * @return int
     */
    public function getId() {
        return $this->get(static::$table_primary_key, null, false);
    }

    /**
     * Set the value of the primary key of the object (aka ID)
     *
     * @param int $id
     */
    public function setId($id) {
        $this->set(static::$table_primary_key, $id, false);
    }

    /**
     * Get the object associated to the property $name.
     *
     * @param string $name The property that holds the foreign key of the object to retrieve
     */
    public function getObject($name) {

        // if the object is not there and exists, let's load it
        if (!isset($this->db_objects[$name]) && isset($this->data[$name]))
        {
            $Class = static::$table_objects[$name];
            $Object = $Class::findById($this->data[$name]);
            if (isset($Object)) {
                $this->db_objects[$name] = $Object;
            }
        }

        return isset($this->db_objects[$name]) ? $this->db_objects[$name] : null;
    }

    /**
     * Set the object associated to the property $name. The property will be set to
     * the primary key of the object.
     *
     * @param string $name The property that holds the foreign key of the given object
     * @param ORM $object The object
     */
    public function setObject($name, $object) {

        if (!isset($object)) {
            $this->set($name, null);
        } else {
            $this->set($name, $object->getId());
            $this->db_objects[$name] = $object;
        }

        return $this;
    }

	public function getData($fields = null) {
		if ($fields == null) {
			return $this->data;
		} else {
			return array_intersect_key($this->data, array_combine($fields, array_fill(0, count($fields), 1)));
		}
	}

	public function getDbData() {
		$data = [];
		foreach (static::getDbFields() as $field => $type) {
			if (array_key_exists($field, $this->data)) {
				$data[$field] = uncast($this->data[$field], static::getFieldType($field));
			}
		}
		return $data;
	}

    /**
     * Saves the entity in the db
     */
	public function save() {
		$pdo = PDOWrapper::get();

		$data = $this->getDbData();

		if (count($data) > 0) {
			$pdo->insert(static::$table_name, $data, true);
		}

		if (!isset($this->data['id'])) {
			$this->data['id'] = (int)$pdo->lastInsertId();
		}
	}

	/**
	 * Same as save(), but only if the object already exists in the DB
	 */
	public function update() {
		if ($this->get('id') !== null) {
			$this->save();
		}
	}

    /**
     * Deletes the entity from the db
     * @return bool
     */
	public function delete() {
		if ($this->getId() !== null) {
			$db = PDOWrapper::get();

			$stmt = $db->prepare("DELETE FROM `".static::$table_name."` WHERE ". self::$table_primary_key ." = :id");
			$stmt->bindValue(':id', $this->getId(), \PDO::PARAM_INT);

			$res = $stmt->execute();

			if ($res) {
				$this->setId(null);
			}

			return $res;
		}
	}

	public function isHashed($mail_field) {
		return is_md5($this->get($mail_field));
	}

	public function hashField($mail_field) {
		$this->set($mail_field, md5($this->get($mail_field)));
	}

    public function offsetExists ($offset) {
	    return array_key_exists($offset, static::$fields);
    }
    public function offsetGet ($offset) {
	    return $this->get($offset);
    }
    public function offsetSet ($offset ,$value) {
	    return $this->set($offset, $value);
    }
    public function offsetUnset ($offset) {
	    return $this->set($offset, null);
    }
}


