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
?>
<link rel="stylesheet" type="text/css" id="themeapp" href="../lib/extjs/resources/css/ext-all-green.css" />

<form action="index.php/buyCredit" method="POST" target="_blank">

<div id="window-1013" class="x-window x-layer x-window-default x-closable x-window-closable x-window-default-closable" style="width: 70%; height: 200px; left: 10px; top: 9px; z-index: 19001;" tabindex="-1">
	<div style="-moz-user-select: -moz-none; width: 100%; left: -1px; top: -1px;" id="window-1013_header" class="x-window-header x-window-header-draggable x-docked x-window-header-default x-horizontal x-window-header-horizontal x-window-header-default-horizontal x-top x-window-header-top x-window-header-default-top x-docked-top x-window-header-docked-top x-window-header-default-docked-top x-unselectable">
		<div style="width: 98%;" id="window-1013_header-body" class="x-window-header-body x-window-header-body-default x-window-header-body-horizontal x-window-header-body-default-horizontal x-window-header-body-top x-window-header-body-default-top x-window-header-body-docked-top x-window-header-body-default-docked-top x-window-header-body-default-horizontal x-window-header-body-default-top x-window-header-body-default-docked-top x-box-layout-ct">
			<div style="width: 98%; height: 17px;" id="window-1013_header-innerCt" class="x-box-inner " role="presentation">
				<div id="window-1013_header-targetEl" style="position:absolute;width:20000px;left:0px;top:0px;height:1px">
				<div id="window-1013_header_hd" class="x-component x-window-header-text-container x-box-item x-component-default" style="text-align: left; left: 0px; top: 0px; margin: 0px; width: 237px;">
					<span id="window-1013_header_hd-textEl" class="x-window-header-text x-window-header-text-default">
						<?php echo Yii::t('yii','Please select the amount and method') ?>
					</span>
				</div>
			</div>
		</div>
	</div>
</div>

<div style="width: 100%; left: 0px; top: 20px; height: 141px;" id="window-1013-body" class="x-window-body x-window-body-default x-closable x-window-body-closable x-window-body-default-closable x-window-body-default x-window-body-default-closable x-layout-fit">
	<div style="margin: 0px; width: 100%; height: 139px;" id="form-1009" class="x-panel x-fit-item x-window-item x-panel-default">
		<div id="form-1009-body" class="x-panel-body x-panel-body-default x-panel-body-default x-box-layout-ct x-docked-noborder-top x-docked-noborder-right x-docked-noborder-bottom x-docked-noborder-left" style="padding: 5px; width: 100%; left: 0px; top: 0px; height: 139px;">
			<div style="height: 129px; width: 100%;" id="form-1009-innerCt" class="x-box-inner " role="presentation">
				<div id="form-1009-targetEl" style="position:absolute;width:20000px;left:0px;top:0px;height:1px">
					

					<table id="combobox-1010" class="x-field x-form-item x-box-item x-field-default x-vbox-form-item" style="border-width: 0px; table-layout: fixed; left: 0px; top: 0px; margin: 0px; width: 278px;" cellpadding="0">
						<tbody>
							<tr id="combobox-1010-inputRow">
								<td id="combobox-1010-labelCell" style="" halign="left" class="x-field-label-cell" valign="top" width="100">
									<label id="combobox-1010-labelEl" for="combobox-1010-inputEl" class="x-form-item-label x-form-item-label-left" style="width:100px;margin-right:5px;">
										<?php echo Yii::t('yii','amount') ?>:
									</label>
								</td>
								<td style="width: 100%;" class="x-form-item-body" id="combobox-1010-bodyEl" role="presentation" colspan="2">
									<select name="amount" class="form_input_select"  >
								<?php
								$arr_purchase_amount = preg_split("/:/", $amount[0]['config_value']);
								foreach ($arr_purchase_amount as $value) :
								?>
									<option value="<?php echo $value ?>"><?php echo $basecurrency[0]['config_value'] . ' ' .round($value,2) ?></option> 
								<?php endforeach; ?>
								</select>
								</td>
							</tr>
						</tbody>
					</table>
					<table id="textfield-1011" class="x-field x-form-item x-box-item x-field-default x-vbox-form-item" style="border-width: 0px; table-layout: fixed; left: 0px; top: 27px; margin: 0px; width: 278px;" cellpadding="0">
						<tbody>
							<tr id="textfield-1011-inputRow">
								<td id="textfield-1011-labelCell" style="" halign="left" class="x-field-label-cell" valign="top" width="100">
									<label id="textfield-1011-labelEl" for="textfield-1011-inputEl" class="x-form-item-label x-form-item-label-left" style="width:100px;margin-right:5px;">
										<?php echo Yii::t('yii','method') ?>
									</label>
								</td>
								<td style="width: 100%;" class="x-form-item-body " id="textfield-1011-bodyEl" role="presentation" colspan="2">
									<select name="method" class="form_input_select"  >
								<?php
								for ($i=0; $i < count($methodPay); $i++):
								?>
									<option value="<?php echo $methodPay[$i]['id'] ?>"><?php echo $methodPay[$i]['payment_method'] ?></option> 
								<?php endfor; ?>
								</select>
								</td>
							</tr>
						</tbody>
					</table>

				</div>
			</div>
		</div>
	</div>
</div>

<div style="width: 290px; left: 4px; top: 165px;" id="toolbar-1014" class="x-toolbar x-docked x-toolbar-footer x-docked-bottom x-toolbar-docked-bottom x-toolbar-footer-docked-bottom x-box-layout-ct">
	<div style="width: 284px; height: 22px;" id="toolbar-1014-innerCt" class="x-box-inner " role="presentation">
		<div id="toolbar-1014-targetEl" style="position:absolute;width:20000px;left:0px;top:0px;height:1px">
			<div id="button-1015" class="x-btn x-box-item x-toolbar-item x-btn-default-small x-noicon x-btn-noicon x-btn-default-small-noicon" style="border-width: 1px; left: 56px; margin: 0px; top: 0px; width: 80px;">
				<em id="button-1015-btnWrap">
					<button  type="submit" style="width: 74px; height: 16px;" id="button-1015-btnEl" type="button" class="x-btn-center" hidefocus="true" role="button" autocomplete="off">
						<span id="button-1015-btnInnerEl" class="x-btn-inner" style="width: 74px;"><?php echo Yii::t('yii','Send') ?></span>
						<span id="button-1015-btnIconEl" class="x-btn-icon "></span>
					</button>
				</em>
			</div>
		</div>
	</div>
</div>

</div>

</form>