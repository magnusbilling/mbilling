<?php
/**
 * Acoes do modulo "CampaignPollInfo".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 28/10/2012
 */

class CampaignPollInfoController extends Controller
{
    public $attributeOrder = 't.id';
    public $extraValues    = array('idCampaignPoll' => 'name');
    public $filterByUser   = 'pkg_campaign.id_user';
    public $join           = 'JOIN pkg_campaign_poll ON pkg_campaign_poll.id = id_campaign_poll JOIN pkg_campaign ON pkg_campaign.id = id_campaign';

    public $pathFileCsv    = 'resources/csv/';
    public $pathFileReport = 'resources/reports/';
    public $nameFileCsv    = 'exported';

    public $fieldsFkReport = array(
        'id_campaign_poll' => array(
            'table'       => 'pkg_campaign_poll',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
    );

    public function init()
    {
        $this->instanceModel = new CampaignPollInfo;
        $this->abstractModel = CampaignPollInfo::model();
        $this->titleReport   = Yii::t('yii', 'Poll Info');
        parent::init();
    }

    public function applyFilterToLimitedAdmin($filter)
    {
        if (Yii::app()->session['user_type'] == 1 && Yii::app()->session['adminLimitUsers'] == true) {
            $this->join .= ' JOIN pkg_user b ON pkg_campaign.id_user = b.id';
            $filter .= " AND b.id_group IN (SELECT id_group FROM pkg_group_user_group
                        WHERE id_group_user = " . Yii::app()->session['id_group'] . " )";
        }
        return $filter;
    }

    public function actionCsv()
    {

        /*if (!Yii::app()->getSession()->get('isAdmin')) {
        parent::actionCsv();
        }
        else{*/
        ini_set("memory_limit", "1024M");

        $_GET['columns'] = preg_replace('/idUserusername/', 'id_user', $_GET['columns']);
        $_GET['columns'] = preg_replace('/idPrefixdestination/', 'id', $_GET['columns']);
        $_GET['columns'] = preg_replace('/idPrefixprefix/', 'id_prefix', $_GET['columns']);
        $_GET['columns'] = preg_replace('/idPhonebookt.name/', 'id_phonebook', $_GET['columns']);
        $_GET['columns'] = preg_replace('/idDiddid/', 'id_did', $_GET['columns']);

        $columns    = json_decode($_GET['columns'], true);
        $filter     = isset($_GET['filter']) ? $this->createCondition(json_decode($_GET['filter'])) : null;
        $fieldGroup = json_decode($_GET['group']);
        $sort       = json_decode($_GET['sort']);

        $arraySort = ($sort && $fieldGroup) ? explode(' ', implode(' ', $sort)) : null;
        $dirGroup  = $arraySort ? $arraySort[array_search($fieldGroup, $arraySort) + 1] : null;
        $firstSort = $fieldGroup ? $fieldGroup . ' ' . $dirGroup . ',' : null;
        $sort      = $sort ? $firstSort . implode(',', $sort) : null;

        $sort = $this->replaceOrder($sort);

        $this->filter = $filter = $this->extraFilter($filter);

        /*array_push($columns, array(
        'header' => 'City',
        'dataIndex' => 'city'
        ));*/

        $records = $this->abstractModel->findAll(array(
            'select'    => $this->getColumnsFromReport($columns),
            'join'      => $this->join,
            'condition' => $filter,
            'order'     => $sort,
            'group'     => 'number',
        ));

        $pathCsv = $this->pathFileCsv . $this->nameFileCsv . time() . '.csv';
        if (!is_dir($this->pathFileCsv)) {
            mkdir($this->pathFileCsv, 777, true);
        }

        $fileOpen  = fopen($pathCsv, 'w');
        $separador = Yii::app()->session['language'] == 'pt_BR' ? ';' : ',';

        $fieldsCsv = array();
        $t         = 0;
        foreach ($records as $numero) {
            if (!Yii::app()->getSession()->get('isAdmin')) {
                $user   = "AND id_user = :id_user";
                $filter = preg_replace("/$user/", "", $filter);
            }

            $sql     = "SELECT * from pkg_campaign_poll_info t WHERE number = :number AND $filter ORDER BY id_campaign_poll ASC";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":id_user", Yii::app()->getSession()->get('id_user'), PDO::PARAM_STR);
            $command->bindValue(":number", $numero->number, PDO::PARAM_STR);
            $optionResult = $command->queryAll();

            $respostas = array();
            $s         = 2;

            if (count($optionResult) == 1 && $optionResult[0]['resposta'] < 0) {
                continue;
            }

            $ids = explode('(', $filter);
            $ids = explode(')', $ids[1]);
            $ids = explode(',', $ids[0]);

            for ($i = 0; $i < count($ids); $i++) {

                //echo $optionResult[$i]['resposta']."\n";
                if (isset($optionResult[$i]['id_campaign_poll']) && in_array($optionResult[$i]['id_campaign_poll'], $ids)) {
                    $respostas[] = $optionResult[$i]['resposta'];
                } else {
                    $respostas[] = '';
                }

            }

            $result = implode(',', $respostas);

            if ($t == 0) {
                $colunas = 'Fecha,Numero,city';

                $filter2 = preg_replace('/id_campaign_poll/', 'id', $filter);

                $filter2 = explode(" AND", $filter2);

                foreach ($filter2 as $key => $value) {
                    if (!preg_match('/id IN/', $value)) {
                        unset($filter2[$key]);
                    }
                }

                $sql2 = "SELECT name from pkg_campaign_poll WHERE " . $filter2[1] . " ORDER BY id ASC";

                $colunnResult = Yii::app()->db->createCommand($sql2)->queryAll();

                foreach ($colunnResult as $key => $coluna) {

                    $colunas .= "," . $coluna['name'];
                }

                fwrite($fileOpen, $colunas . "\n");
                $t++;
            }

            $fieldsCsv2 = $optionResult[0]['date'] . ',' . $numero->number . ',' . $numero->city . ",$result\n";

            fwrite($fileOpen, $fieldsCsv2);

        }

        fclose($fileOpen);

        header('Content-type: application/csv');
        header('Content-Disposition: inline; filename="' . $pathCsv . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        ob_clean();
        flush();
        if (readfile($pathCsv)) {
            unlink($pathCsv);
        }

        //}
    }

    public function extraFilter($filter)
    {
        $filter = isset($this->filter) ? $filter . $this->filter : $filter;

        $filter = $filter . ' AND ' . $this->defaultFilter;
        $filter = $this->filterReplace($filter);

        if (Yii::app()->getSession()->get('user_type') > 2 && $this->filterByUser) {
            $filter .= ' AND ' . $this->defaultFilterByUser . ' = ' . Yii::app()->getSession()->get('id_user');
        } elseif (Yii::app()->getSession()->get('user_type') == 2) {
            $filter .= ' AND pkg_campaign.id_user IN (SELECT id FROM pkg_user WHERE id_user = "' . Yii::app()->getSession()->get('id_user') . '") ';
        }
        return $filter;
    }

    public function actionRead($asJson = true)
    {
        parent::actionRead($asJson = true);

    }

    public function recordsExtraSum($select, $join, $filter, $group, $limit, $records)
    {
        $selectSum = 'SELECT resposta AS resposta2, COUNT( resposta ) AS sumresposta FROM ' . $this->abstractModel->tableName() . ' t
                    ' . $this->join . ' WHERE ' . $filter . ' GROUP BY resposta ORDER BY resposta DESC';

        $this->nameSum = 'sum';
        return $this->abstractModel->findAllBySql($selectSum);
    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item) {
            $attributes[$key]                = $item->attributes;
            $attributes[$key]['sumresposta'] = $item->sumresposta;
            $attributes[$key]['resposta2']   = $item->resposta2;

            if (isset($_SESSION['isClient']) && $_SESSION['isClient']) {
                foreach ($this->fieldsInvisibleClient as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            if (isset($_SESSION['isAgent']) && $_SESSION['isAgent']) {
                foreach ($this->fieldsInvisibleAgent as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            foreach ($itemsExtras as $relation => $fields) {
                $arrFields = explode(',', $fields);
                foreach ($arrFields as $field) {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if ($_SESSION['isClient']) {
                        foreach ($this->fieldsInvisibleClient as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }

                    if ($_SESSION['isAgent']) {
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
