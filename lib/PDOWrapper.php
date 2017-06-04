<?php

class PDOWrapper extends PDO
{

	protected static $pdo;

	public static function get() {
		$config = Config::get();

		if (!self::$pdo instanceof PDOWrapper) {
			try {

				self::$pdo = new PDOWrapper('mysql:host='.$config['database']['hostname'].';dbname='.$config['database']['database'], $config['database']['username'], $config['database']['password'], [
					PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
				]);
				self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


			} catch (\Exception $e) {
				throw $e;
			}
		}

		return self::$pdo;
	}

	public function bindParams($sql, $params) {
		$stmt_params = [];
		$n = 0;
		foreach ($params as $param => $value) {
			if (is_array($value)) { // process array params
				$qms = [];
				foreach ($value as $v) {
					$stmt_params[":q$n"] = $v;
					$qms[] = ":q$n";
					$n++;
				}
				$sql = str_replace($param, join(',', $qms), $sql);
			} else {
				$stmt_params[$param] = $value;
			}
		}

		$stmt = $this->prepare($sql);
		foreach ($stmt_params as $param => $value) {
			$stmt->bindValue($param, $value, self::getType($value));
		}

		return $stmt;
	}

	public function fetch($sql, $params = [], $fetch_mode = PDO::FETCH_ASSOC) {
		try {

			$stmt = $this->bindParams($sql, $params);
			$stmt->execute();

			if ($fetch_mode == PDO::FETCH_COLUMN) {
				return $stmt->fetchColumn();
			} else {
				return $stmt->fetch($fetch_mode);
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function fetchAll($sql, $params = [], $fetch_mode = PDO::FETCH_ASSOC) {
		try {

			$stmt = $this->bindParams($sql, $params);
			$stmt->execute();

			$rows = [];
			if ($fetch_mode == PDO::FETCH_COLUMN) {
				while ($row = $stmt->fetchColumn()) {
					$rows[] = $row;
				}
			} else {
				while ($row = $stmt->fetch($fetch_mode)) {
					$rows[] = $row;
				}
			}

			return $rows;

		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function insert($table, $fields, $on_duplicate_key_update = true) {

		$sql = "insert into `$table` (`".join('`, `', array_keys($fields))."`) values (:".join(', :', array_keys($fields)).") ";

		if ($on_duplicate_key_update) {
			$sql .= "on duplicate key update ".join(', ', array_map(function ($field) {return "`$field` = VALUES(`$field`)";}, array_keys($fields)));
		}

		try {

			$stmt = $this->prepare($sql);

			foreach ($fields as $field => $value) {
				$stmt->bindValue(":$field", $value, self::getType($value));
			}

			return $stmt->execute();

		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function update($table, $fields, $where) {
		$params = [];
		foreach ($fields as $field => $value) {
			$params[':'.$field] = $value;
		}

		$stmt = $this->prepare("update `$table` set ".join(', ', array_map(function ($field) {return "`$field` = :$field";}, array_keys($fields)))." where $where");

		return $stmt->execute($params);
	}


	/**
	 * @param mixed $var
	 * @return int
	 */
	public static function getType($var) {
		if ($var === true || $var === false) {
			return \PDO::PARAM_BOOL;
		} elseif ($var === (int)$var || $var == (int)$var && strlen($var) == strlen((int)$var)) {
			return \PDO::PARAM_INT;
		} elseif ($var === null) {
			return \PDO::PARAM_NULL;
		} else {
			return \PDO::PARAM_STR;
		}
	}

	/** @inheritdoc */
	public function exec($statement, $params = []) {
		$stmt = $this->bindParams($statement, $params);
		return $stmt->execute();
	}

}




