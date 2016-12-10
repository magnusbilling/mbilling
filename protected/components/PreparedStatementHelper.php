<?php


class PreparedStatementHelper{
	static public function arrayToParams(array $array){
		$result = array();
		foreach ($array as $key => $value) {
			$result[] = ":param".$key; 
		}

		return implode(",", $result);
	}

	static public function bindArrayParams(array $array, &$command,$paramType=PDO::PARAM_STR){

		foreach ($array as $key => $value) {
			$command->bindValue(":param".$key,$value,$paramType);
		}
	}

}