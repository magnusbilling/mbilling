<?php
/**
 * Acoes do modulo "Iax".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 23/06/2016
 */

class IaxController extends Controller
{
	public $attributeOrder     = 'regseconds DESC';
	public $extraValues        = array( 'idUser' => 'username' );

	public $filterByUser        = true;
	public $defaultFilterByUser = 'b.id_user';
	public $join                = 'JOIN pkg_user b ON t.id_user = b.id';

	private $host = 'localhost';
    	private $user = 'magnus';
    	private $password = 'magnussolution';

	public $fieldsFkReport = array(
		'id_user' => array(
			'table' => 'pkg_user',
			'pk' => 'id',
			'fieldReport' => 'username'
		)
	);


	public function init() {
		$this->instanceModel = new Iax;
		$this->abstractModel = Iax::model();
		parent::init();
	}

	public function extraFilter( $filter ) {
		$filter = isset( $this->filter ) ? $filter.$this->filter : $filter;

		$filter = $filter . ' AND ' .$this->defaultFilter;
		$filter = $this->filterReplace( $filter );

		if ( Yii::app()->getSession()->get( 'user_type' )  > 1 && $this->filterByUser ) {
			$filter .= ' AND ('. $this->defaultFilterByUser . ' = '.Yii::app()->getSession()->get( 'id_user' );
			$filter .= ' OR t.id_user = '.Yii::app()->getSession()->get( 'id_user' ).')';
		}
		return $filter;
	}
	public function getAttributesModels($models, $itemsExtras = array())
    	{

    		$asmanager = new AGI_AsteriskManager;
        	$asmanager->connect($this->host, $this->user, $this->password);
        	

        	$attributes = false;
        	foreach ($models as $key => $item)
        	{
            	$attributes[$key]                         = $item->attributes;
            	
            	$server = $asmanager->Command("iax2 show peer $item->name");
        		$arr = explode("\n", $server["data"]);
			$arr3 = explode("Addr->IP:", preg_replace("/ /", "", $arr[17]));
			$ipaddr = explode("Port", trim(rtrim($arr3[1])));
			$ipaddr = $ipaddr[0];
               
        		

               $attributes[$key]['ipaddr'] = $ipaddr; 

            	if(isset($_SESSION['isClient']) && $_SESSION['isClient'])
            	{
                	foreach ($this->fieldsInvisibleClient as $field) {
                    unset($attributes[$key][$field]);
                	}
            	}
            
	            if(isset($_SESSION['isAgent']) && $_SESSION['isAgent'])
	            {
	                foreach ($this->fieldsInvisibleAgent as $field) {
	                    unset($attributes[$key][$field]);
	                }
	            }


            foreach($itemsExtras as $relation => $fields)
            {
                $arrFields = explode(',', $fields);
                foreach($arrFields as $field)
                {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if($_SESSION['isClient']) {
                        foreach ($this->fieldsInvisibleClient as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }

                    if($_SESSION['isAgent']) {
                        foreach ($this->fieldsInvisibleAgent as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }

        return $attributes;
    }
}
