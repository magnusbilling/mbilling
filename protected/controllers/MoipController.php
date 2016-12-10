<?php

/**
 * Url for moip ruturn http://ip/billing/index.php/moip .
 * Url para configurar moip https://www.moip.com.br/AdmMainMenuMyData.do?method=transactionnotification
 * Meus dados -> Preferencia -> uNotificação das transações.
 * https://www.moip.com.br/PagamentoMoIP.do
 */
class MoipController extends BaseController
{

	public function actionIndex()
	{
		if (isset($_POST['id_transacao']))
		{
			$status_pagamento = $_POST['status_pagamento'];
			$monto = $_POST['valor'];
			$tipopagamento = $_POST['tipo_pagamento'];
			$codigo = $_POST['cod_moip'];
			$usuario = explode("-", $_POST['id_transacao']);
			$usuario = trim($usuario[1]);
			$monto = substr($monto, 0, -2);
			$description = $tipopagamento . ', Nro. de transação MOIP ' . $codigo;
			if ($status_pagamento == 1)
			{
				exit;
				$sql = "SELECT * FROM pkg_user WHERE username = :usuario";
				$command = Yii::app()->db->createCommand($sql);
				$command->bindValue(":usuario", $usuario, PDO::PARAM_STR);
				$resultUser = $command->queryAll();

				if (count($resultUser) > 0 ) 
				{
					Process ::releaseUserCredit($resultUser[0]['id'], $monto, $description, $codigo);						
				}
		    }
		}
	}
}