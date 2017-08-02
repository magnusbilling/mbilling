<?php
/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2016 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */

class Model extends CActiveRecord
{

	public function getModule(){
		return $this->_module;
	}

	 

	public function number_translation($translation,$destination,$base_country)
    	{
  
		$regexs = preg_split("/,/", $translation);

		foreach ($regexs as $key => $regex) {

			$regra = preg_split( '/\//', $regex );
			$grab = isset($regra[0]) ? $regra[0] : '';
			$replace = isset($regra[1]) ? $regra[1] : '';
			$digit = isset($regra[2]) ? $regra[2] : '';		    

			$number_prefix = substr($destination,0,strlen($grab));

			if (strtoupper($base_country) == 'BRL' || strtoupper($base_country) == 'ARG')
			{
				if ($grab == '*' && strlen($destination) == $digit) {
					$destination = $replace.$destination;
				}
				else if (strlen($destination) == $digit && $number_prefix == $grab) {
					$destination = $replace.substr($destination,strlen($grab));
				}
				elseif ($number_prefix == $grab)
				{
					$destination = $replace.substr($destination,strlen($grab));
				}

			}else{                  

				if (strlen($destination) == $digit) {           
					if ($grab == '*' && strlen($destination) == $digit) {
						$destination = $replace.$destination;
					}
					else if ( $number_prefix == $grab) {
						$destination = $replace.substr($destination,strlen($grab));
					}
				} 
			}     

  
        	}
 		return $destination;
    	}
}