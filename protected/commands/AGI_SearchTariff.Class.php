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
class SearchTariff {
    public function find( &$MAGNUS, $agi, $Calc ) {

        $sql = "SELECT length(prefix) as prefix FROM pkg_prefix WHERE prefix LIKE '".substr( $MAGNUS->destination, 0, 2 )."%' ORDER BY length(prefix) DESC LIMIT 1";
        $agi->verbose( $sql, 25 );
        $resultPrefixLen = Yii::app()->db->createCommand( $sql )->queryAll();

        if ( !is_array( $resultPrefixLen ) || count( $resultPrefixLen ) == 0 )
            return 0;
        
        $max_len_prefix = $resultPrefixLen[0]['prefix'];

        $prefixclause = '(';
        while ( $max_len_prefix >= 1 ) {
            $prefixclause .= "prefix='" . substr( $MAGNUS->destination, 0, $max_len_prefix ) . "' OR ";
            $max_len_prefix--;
        }
        $prefixclause = substr( $prefixclause, 0, -3 ).")";

        $agi->verbose( $prefixclause, 20 );

        $sql = "SELECT " .
            "lcrtype, pkg_plan.id AS id_plan, pkg_prefix.prefix AS dialprefix, " .
            "pkg_plan.name, pkg_rate.id_prefix, " .
            "pkg_rate.id AS id_rate, buyrate,  buyrateinitblock buyrateinitblock, " .
            "buyrateincrement, rateinitial, initblock, " .
            "billingblock, connectcharge, disconnectcharge disconnectcharge," .
            "pkg_rate.id_trunk AS id_trunk, pkg_trunk.trunkprefix AS rc_trunkprefix, pkg_trunk.directmedia AS rc_directmedia," .
            "pkg_trunk.providertech AS rc_providertech ,pkg_trunk.providerip AS rc_providerip, pkg_trunk.removeprefix AS rc_removeprefix, " .
            "pkg_trunk.failover_trunk AS rt_failover_trunk, pkg_trunk.addparameter AS rt_addparameter_trunk,  " .
            "pkg_trunk.status, pkg_trunk.inuse, pkg_trunk.maxuse, pkg_trunk.allow_error,  " .
            "pkg_trunk.if_max_use, pkg_rate.additional_grace AS additional_grace, minimal_time_charge, minimal_time_buy, " .
            "pkg_trunk.link_sms, pkg_trunk.user user, pkg_trunk.secret, package_offer , pkg_trunk.id_provider, pkg_provider.credit_control, pkg_provider.credit " .
            "FROM pkg_plan " .
            "LEFT JOIN pkg_rate ON pkg_plan.id = pkg_rate.id_plan " .
            "LEFT JOIN pkg_trunk AS pkg_trunk ON pkg_trunk.id = pkg_rate.id_trunk " .
            "LEFT JOIN pkg_prefix ON pkg_rate.id_prefix = pkg_prefix.id " .
            "LEFT JOIN pkg_provider ON pkg_trunk.id_provider = pkg_provider.id " .
            "WHERE pkg_plan.id=$MAGNUS->id_plan AND pkg_rate.status = 1 AND $prefixclause " .
            "ORDER BY pkg_prefix.prefix DESC";
        $agi->verbose( $sql, 25 );
        $result = Yii::app()->db->createCommand( $sql )->queryAll();


        if ( !is_array( $result ) || count( $result ) == 0 )
            return 0;


        /*1) REMOVE THOSE THAT HAVE A SMALLER DIALPREFIX */
        $max_len_prefix = strlen( $result[0]['dialprefix'] );
        for ( $i = 1; $i < count( $result ); $i++ ) {
            if ( strlen( $result[$i]['dialprefix'] ) < $max_len_prefix )
                break;
        }

        $result = array_slice( $result, 0, $i );

        if ( count( $result ) > 1 ) {
            if ( $result[0]['lcrtype'] == 2 )
                $result = SearchTariff::load_balancer( $MAGNUS, $agi, $result );
            else if ( $result[0]['lcrtype'] == 1 )
                    $result = $Calc->array_csort( $result, 'buyrate', SORT_ASC );
                else
                    $result = $Calc->array_csort( $result, 'rateinitial', SORT_ASC );
        }

        /* 3) REMOVE THOSE THAT USE THE SAME TRUNK - MAKE A DISTINCT */
        /*    AND THOSE THAT ARE DISABLED. */
        $mylistoftrunk = array();

        /*Select custom rate to user*/
        $sql = "SELECT rateinitial, initblock, billingblock FROM pkg_user_rate WHERE id_prefix = ". $result[0]['id_prefix'] ." AND id_user = ". $MAGNUS->id_user;
        $resultUserRate = Yii::app()->db->createCommand( $sql )->queryAll();
        $agi->verbose( $sql, 25 );

        for ( $i = 0; $i < count( $result ); $i++ ) {
            /*change custom rate to user*/
            if ( count( $resultUserRate ) > 0 ) {
                $result[$i]['rateinitial']  = $resultUserRate[0]['rateinitial'];
                $result[$i]['initblock']    = $resultUserRate[0]['initblock'];
                $result[$i]['billingblock'] = $resultUserRate[0]['billingblock'];
            }

            $status = $result[$i]['status'];/*status trunk*/
            $mylistoftrunk_next[] = $mycurrenttrunk = $result[$i]['id_trunk'];

            /* Check if we already have the same trunk in the ratecard*/
            if ( ( $i == 0 || !in_array( $mycurrenttrunk, $mylistoftrunk ) ) ) {
                $distinct_result[] = $result[$i];
            }
            if ( $status == 1 )
                $mylistoftrunk[] = $mycurrenttrunk;
        }

        $Calc->tariffObj = $distinct_result;
        $Calc->number_trunk = count( $distinct_result );/*total de troncos*/

        $agi->verbose( "NUMBER TRUNK FOUND" . $Calc->number_trunk, 10 );
        return 1;
    }

    public function load_balancer( &$MAGNUS, &$agi, $result ) {
        $agi->verbose( 'Load Balancer', 15 );
        $total = count( $result );

        $sql = 'SELECT * FROM pkg_balance WHERE id_prefix = '. $result[0]['dialprefix'];
        $agi->verbose( $sql, 25 );
        $result2    = Yii::app()->db->createCommand( $sql )->queryAll();
        if ( count( $result2 ) == 0 ) {
            $sql    = "INSERT INTO pkg_balance (id_prefix, last_use) VALUE ('".$result[0]['dialprefix']."', '0')";
            $agi->verbose( $sql, 25 );
            Yii::app()->db->createCommand( $sql )->execute();
            $ultimo = 0;
        }else {
            $ultimo = $result2[0]['last_use'];

            if ( $ultimo  == $total - 1 ) {
                $sql = "UPDATE pkg_balance SET last_use = 0 WHERE id_prefix = ". $result[0]['dialprefix'];
            }else {
                $sql = "UPDATE pkg_balance SET last_use = last_use + 1 WHERE id_prefix = ". $result[0]['dialprefix'];
            }
            $agi->verbose( $sql, 25 );
            Yii::app()->db->createCommand( $sql )->execute();
        }

        //coloca o id ultimo em primeiro
        $result = array_filter( array_merge( array( $result[$ultimo] ), $result ) );

        //retira o id dublicado
        for ( $i=0; $i <= $total; $i++ ) {
            if ( $i > 0 ) {
                if ( $result[$i]['id_rate'] == $result[0]['id_rate'] ) {
                    unset( $result[$i] );
                }
            }
        }
        $result = array_values( $result );
        foreach ( $result as $key => $value )
            $agi->verbose( $key . ' => ' .print_r( $value['id_rate'], true ), 15 );


        return $result;
    }

}
?>
