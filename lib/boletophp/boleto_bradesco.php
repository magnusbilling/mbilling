<?php
// +----------------------------------------------------------------------+
// | BoletoPhp - Versão Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo está disponível sob a Licença GPL disponível pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Você deve ter recebido uma cópia da GNU Public License junto com     |
// | esse pacote; se não, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colaborações de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do    |
// | PHPBoleto de João Prado Maia e Pablo Martins F. Costa                |
// |                                                                      |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto Bradesco: Ramon Soares                        |
// +----------------------------------------------------------------------+


// ------------------------- DADOS DINÂMICOS DO SEU CLIENTE PARA A GERAÇÃO DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulário c/ POST, GET ou de BD (MySql,Postgre,etc) //

// DADOS DO BOLETO PARA O SEU CLIENTE
// DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento           = 5;
$taxa_boleto                            = isset($taxa_boleto) ? $taxa_boleto : 0;
$data_venc                              = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
$valor_cobrado                          = isset($valor_cobrado) ? $valor_cobrado : "2950,00"; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado                          = str_replace(",", ".",$valor_cobrado);
$valor_boleto                           =number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

$dadosboleto["nosso_numero"]            = isset($convenio) ? $convenio : "87654";
$dadosboleto["numero_documento"]        = isset($numero_documento) ? $numero_documento : "27.030195.10";  // Num do pedido ou do documento
$dadosboleto["data_vencimento"]         = isset($data_vencimento) ? $data_vencimento : $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"]          = isset($data_documento) ? $data_documento : date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"]      = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"]            = $valor_boleto;  // Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"]                  = isset($sacado) ? $sacado : "Nome do seu Cliente";
$dadosboleto["endereco1"]               = isset($endereco1) ? $endereco1 :"Endereço do seu Cliente";
$dadosboleto["endereco2"]               = isset($endereco2) ? $endereco2 :"Cidade - Estado -  CEP: 00000-000";

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"]          = isset($demonstrativo1) ? $demonstrativo1 : "";
$dadosboleto["demonstrativo2"]          = isset($demonstrativo2) ? $demonstrativo2 : "";
$dadosboleto["demonstrativo3"]          = isset($demonstrativo3) ? $demonstrativo3 : "";

// INSTRUÇÕES PARA O CAIXA
$dadosboleto["instrucoes1"]             = isset($instrucoes1) ? $instrucoes1 : "";
$dadosboleto["instrucoes2"]             = isset($instrucoes2) ? $instrucoes2 : "";
$dadosboleto["instrucoes3"]             = isset($instrucoes3) ? $instrucoes3 : "";
$dadosboleto["instrucoes4"]             = isset($instrucoes4) ? $instrucoes4 : "";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"]              = isset($quantidade) ? $quantidade :"001";
$dadosboleto["valor_unitario"]          = isset($valor_boleto) ? $valor_boleto :"10";
$dadosboleto["aceite"]                  = isset($aceite) ? $aceite :"";   
$dadosboleto["especie"]                 = isset($especie) ? $especie :"R$";
$dadosboleto["especie_doc"]             = isset($especie_doc) ? $especie_doc :"DS";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //

$agencia                                = explode("-",$agencia);
$conta                                  = explode("-",$conta);
$convenio                               = explode("-",$convenio);

// DADOS DA SUA CONTA - Bradesco
$dadosboleto["agencia"]                 = $agencia[0]; // Num da agencia, sem digito
$dadosboleto["agencia_dv"]              = $agencia[1]; // Digito do Num da agencia
$dadosboleto["conta"]                   = $conta[0];  // Num da conta, sem digito
$dadosboleto["conta_dv"]                = $conta[1];  // Digito do Num da conta

// DADOS PERSONALIZADOS - Bradesco
$dadosboleto["conta_cedente"]           = $conta[0];
 // ContaCedente do Cliente, sem digito (Somente N&uacute;meros)
$dadosboleto["conta_cedente_dv"]        = $conta[1]; // Digito da ContaCedente do Cliente
$dadosboleto["carteira"]                = $carteira;  // C�digo da Carteira


// TIPO DO BOLETO
$dadosboleto["formatacao_convenio"]     = isset($formatacao_convenio) ? $formatacao_convenio : "7"; // REGRA: 8 p/ Convênio c/ 8 dígitos, 7 p/ Convênio c/ 7 dígitos, ou 6 se Convênio c/ 6 dígitos
$dadosboleto["formatacao_nosso_numero"] = isset($formatacao_nosso_numero) ? $formatacao_nosso_numero : "2"; // REGRA: Usado apenas p/ Convênio c/ 6 dígitos: informe 1 se for NossoNúmero de até 5 dígitos ou 2 para opção de até 17 dígitos



// SEUS DADOS
$dadosboleto["identificacao"]           = isset($identificacao) ? $identificacao : "Compra de creditos por Boletos";;
$dadosboleto["cpf_cnpj"]                = isset($cpf_cnpj) ? $cpf_cnpj : "";
$dadosboleto["endereco"]                = isset($endereco) ? $endereco : "Coloque o endereço da sua empresa aqui";
$dadosboleto["cidade_uf"]               = isset($cidade_uf) ? $cidade_cidade. ' - ' .$cidade_uf : "Cidade / Estado";
$dadosboleto["cedente"]                 = isset($cedente) ? $cedente : "Coloque a Razão Social da sua empresa aqui";

// NÃO ALTERAR!
include("include/funcoes_bradesco.php"); 
include("include/layout_bradesco.php");
?>
