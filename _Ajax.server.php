<?php
require("_Ajax.comun.php"); // No modificar esta linea
include_once './mayorizacion.inc.php';

/*:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
// S E R V I D O R   A J A X //
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/**
Herramientas de apoyo 
 */

//LINEA DIRECTORIO CHEQUES PROTESTATDOS

function agrega_dir_che()
{

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$idEmpresa = $_SESSION['U_EMPRESA'];
    $idSucursal = $_SESSION['U_SUCURSAL'];

	$oReturn = new xajaxResponse();

	$sql="select tran_cod_tran from saetran where tran_prot_tran='S' and tran_cod_empr=$idEmpresa and tran_cod_sucu=$idSucursal";
	$tran = consulta_string_func($sql, 'tran_cod_tran', $oIfx, '');

	$oReturn->assign('tran', 'value', $tran );
	$oReturn->script('anadir_dir_cheq()');

	return $oReturn;
}

//INSERCION LINEAS DE DIRECTORIO CHEQUES PROTESTADOS

function agrega_modifica_grid_dir_cheq($nTipo = 0, $aForm = '', $total_cheque = 0, $comision = 0, $actividad = '', $num_cheque = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$aDataGrid = $_SESSION['aDataGirdDir'];
	$aDataDiar = $_SESSION['aDataGirdDiar'];

	$aLabelGrid = array(
		'Id', 'Cliente', 'SubCliente', 'Tipo', 'Factura', 'Fec. Vence', 'Detalle', 'Cotizacion',
		'Debito Moneda Local', 'Credito Moneda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 'Modificar', 'Eliminar',		'DI'
	);

	$aLabelDiar = array(
		'Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar',
		'Centro Costo', 			'Centro Actividad',	'DIR',		'RET'
	);

	$oReturn = new xajaxResponse();

	// VARIABLES
	$tran_cod 	= $aForm["tran"];
	$detalle  	= $aForm["det_dir"];
	$doc      	= $aForm["documento"];
	$idsucursal = $aForm["sucursal"];

	if (empty($idempresa)) {
		$idempresa = $aForm["empresa"];
	}

	if (empty($mone_cod)) {
		$mone_cod = $aForm["moneda"];
	}

	$sql      = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
	$mone_base = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

	if ($mone_cod == $mone_base) {
		$sql      = "select pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa ";
		$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

		$sql = "select tcam_val_tcam from saetcam where
				mone_cod_empr = $idempresa and
				tcam_cod_mone = $mone_extr and
				tcam_fec_tcam in (
									select max(tcam_fec_tcam)  from saetcam where
											mone_cod_empr = $idempresa and
											tcam_cod_mone = $mone_extr
								)  ";

		$coti = $aForm["cotizacion_ext"];
	} else {
		$coti = $aForm["cotizacion"];
	}

	if ($nTipo == 0) {

		$cont = 0;
		$contd = 0;

		///ARRAY DE FACTURAS Y VALORES

		//$array_fact = explode('/', $array);


		//foreach ($array_fact as $nfact) {


		//if (!empty($nfact)) {

		//$array_detfact = explode(':', $nfact);


		//$fact_num = trim($array_detfact[0]);

		/*$txt  		= abs(str_replace(",", "", $array_detfact[1]));
				if ($cont == 0) {
					$txt += $comision;
				}*/

		$txt = $total_cheque;

		$fact_num = $num_cheque;




		//$ix        = $val[0];
		//$fact_num  = $aForm['factura'];
		$fec_emis  = $aForm['fecha'];
		$fec_venc  = $aForm['fecha'];
		$clpv_cod  = $aForm['clpv_cod'];
		$tran_cod  = $aForm['tran'];
		$det_dir   = $aForm['det_dir'];

		// CUENTAS DE CLPV
		$sql       = "select clpv_cod_cuen, grpv_cod_grpv, clpv_clopv_clpv from saeclpv 
										where clpv_cod_empr = $idempresa and 
										clpv_cod_clpv 		= $clpv_cod ";
		$tipo      = consulta_string_func($sql, 'clpv_clopv_clpv', $oIfx, '');

		if ($tipo == 'CL') {
			$modulo = 3;
		} elseif ($tipo == 'PV') {
			$modulo = 4;
		}

		$sql = "select tran_cod_cuen from saetran where 
								  tran_cod_tran = '$tran_cod' and 
								  tran_cod_modu = $modulo and
								  tran_cod_sucu = $idsucursal and
								  tran_cod_empr = $idempresa and
								( tran_ant_tran = 1 or 
								  tran_cie_tran = 1   
								) ";
		$clpv_cuen = consulta_string_func($sql, 'tran_cod_cuen', $oIfx, '');

		if (empty($clpv_cuen)) {
			$sql       = "select clpv_cod_cuen, grpv_cod_grpv, clpv_clopv_clpv from saeclpv 
										where clpv_cod_empr = $idempresa and 
										clpv_cod_clpv 		= $clpv_cod ";
			$clpv_cuen = consulta_string_func($sql, 'clpv_cod_cuen', $oIfx, '');
			if (empty($clpv_cuen)) {
				$clpv_gr = consulta_string_func($sql, 'grpv_cod_grpv', $oIfx, '');
				$sql = "select  grpv_cta_grpv  from saegrpv where
										grpv_cod_empr = $idempresa and
										grpv_cod_grpv = '$clpv_gr' ";
				$clpv_cuen = consulta_string_func($sql, 'grpv_cta_grpv', $oIfx, '');
			}
		}

		// NOMBRE CUENtA
		$sql = "select cuen_nom_cuen from saecuen where 
			                    cuen_cod_empr = $idempresa and
			                    cuen_cod_cuen = '$clpv_cuen' ";
		$cuen_nom = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

		$ccli  	   = $aForm['ccli'];
		//$txt  		= abs(str_replace(",", "", $aForm['fact_valor']));


		$valDirCre = 0;
		$valDiaCre = 0;
		$valDirDeb = 0;
		$valDiaDeb = 0;



		//tipo de transaccion
		$sql = "select trans_tip_tran from saetran where tran_cod_tran = '$tran_cod' and tran_cod_modu = $modulo and tran_cod_empr = $idempresa ";
		$trans_tip_tran = consulta_string($sql, 'trans_tip_tran', $oIfx, '');

		//$oReturn->alert($sql);

		if ($trans_tip_tran == 'DB') {
			$valDirDeb = $txt;
			$valDiaDeb = $txt;
		} elseif ($trans_tip_tran == 'CR') {
			$valDirCre = $txt;
			$valDiaCre = $txt;
		}

		// DIRECTORIO
		//$cont = count($aDataGrid);
		$aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
		$aDataGrid[$cont][$aLabelGrid[1]] = $clpv_cod;
		$aDataGrid[$cont][$aLabelGrid[2]] = $ccli;
		$aDataGrid[$cont][$aLabelGrid[3]] = $tran_cod;
		$aDataGrid[$cont][$aLabelGrid[4]] = $fact_num;
		$aDataGrid[$cont][$aLabelGrid[5]] = $fec_venc;
		$aDataGrid[$cont][$aLabelGrid[6]] = $det_dir;
		$aDataGrid[$cont][$aLabelGrid[7]] = $coti;

		if ($mone_cod == $mone_base) {
			// moneda local
			$cre_tmp = 0;
			$deb_tmp = 0;
			if ($coti > 0) {
				$cre_tmp = round(($valDirCre / $coti), 2);
			}

			if ($coti > 0) {
				$deb_tmp = round(($valDirDeb / $coti), 2);
			}

			$aDataGrid[$cont][$aLabelGrid[8]] = $valDirDeb;
			$aDataGrid[$cont][$aLabelGrid[9]] = $valDirCre;
			$aDataGrid[$cont][$aLabelGrid[10]] = $deb_tmp;
			$aDataGrid[$cont][$aLabelGrid[11]] = $cre_tmp;
		} else {
			// moneda extra

			$aDataGrid[$cont][$aLabelGrid[8]] = $valDirDeb * $coti;
			$aDataGrid[$cont][$aLabelGrid[9]] = $valDirCre * $coti;

			$aDataGrid[$cont][$aLabelGrid[10]] = $valDirDeb;
			$aDataGrid[$cont][$aLabelGrid[11]] = $valDirCre;
		}

		$aDataGrid[$cont][$aLabelGrid[12]] = '';
		//$contd = count($aDataDiar);
		$aDataGrid[$cont][$aLabelGrid[13]] = '<div align="center">
																<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																title = "Presione aqui para Eliminar"
																style="cursor: hand !important; cursor: pointer !important;"
																onclick="javascript:xajax_elimina_detalle_dir(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
																alt="Eliminar"
																align="bottom" />
															</div>';
		$aDataGrid[$cont][$aLabelGrid[14]] = $contd;

		// DIARIO
		//echo $coti;exit;
		$aDataDiar[$contd][$aLabelDiar[0]] = floatval($contd);
		$aDataDiar[$contd][$aLabelDiar[1]] = $clpv_cuen;
		$aDataDiar[$contd][$aLabelDiar[2]] = $cuen_nom;
		$aDataDiar[$contd][$aLabelDiar[3]] = $doc;
		$aDataDiar[$contd][$aLabelDiar[4]] = $coti;

		if ($mone_cod == $mone_base) {
			// moneda local
			$cre_tmp = 0;
			$deb_tmp = 0;
			if ($coti > 0) {
				$cre_tmp = round(($valDirCre / $coti), 2);
			}

			if ($coti > 0) {
				$deb_tmp = round(($valDirDeb / $coti), 2);
			}

			$aDataDiar[$contd][$aLabelDiar[5]] = $valDiaDeb;
			$aDataDiar[$contd][$aLabelDiar[6]] = $valDiaCre;
			$aDataDiar[$contd][$aLabelDiar[7]] = $deb_tmp;
			$aDataDiar[$contd][$aLabelDiar[8]] = $cre_tmp;
		} else {
			// moneda extra
			$aDataDiar[$contd][$aLabelDiar[5]] = $valDiaDeb * $coti;
			$aDataDiar[$contd][$aLabelDiar[6]] = $valDiaCre * $coti;
			$aDataDiar[$contd][$aLabelDiar[7]] = $valDirDeb;
			$aDataDiar[$contd][$aLabelDiar[8]] = $valDirCre;
		}


		$aDataDiar[$contd][$aLabelDiar[9]] = $det_dir;
		$aDataDiar[$contd][$aLabelDiar[10]] = '';
		$aDataDiar[$contd][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																		title = "Presione aqui para Eliminar"
																		style="cursor: hand !important; cursor: pointer !important;"
																		onclick="javascript:xajax_elimina_detalle_dir(' . $contd . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
																		alt="Eliminar"
																		align="bottom" />';
		$aDataDiar[$contd][$aLabelDiar[12]] = '';
		$aDataDiar[$contd][$aLabelDiar[13]] = '';
		$aDataDiar[$contd][$aLabelDiar[14]] = $cont;



		//$cont++;
		//$contd++;
		//} //CIERRE IF NFACT





		//} //CIERRE FOREACH


	} //CIERRE IF NTIPO


	///////////////////////////////////////////////////////////////

	$oReturn->assign("actividad", "value", $actividad);

	$_SESSION['aDataGirdDir'] = $aDataGrid;
	$sHtml = mostrar_grid_dir($idempresa, $idsucursal);
	$oReturn->assign("divDir", "innerHTML", $sHtml);

	// DIARIO
	$sHtml = '';
	$_SESSION['aDataGirdDiar'] = $aDataDiar;
	$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
	$oReturn->assign("divDiario", "innerHTML", $sHtml);

	//$oReturn->alert($sHtml);




	// TOTAL DIARIO
	$oReturn->script("total_diario();");
	$oReturn->script("cerrar_ventana();");
	$oReturn->assign('tran', 'value', 0);
	$oReturn->assign('ccli', 'value', 0);
	$oReturn->script('anadir_dasi_che()');


	return $oReturn;
}

//LINEA DIARIO CHEQUE PROTESTADO
function agrega_diario_che($cheq)
{

	session_start();

	$oReturn = new xajaxResponse();

	$oReturn->assign('documento', 'value', $cheq);
	$oReturn->script('anadir_dasi()');
	return $oReturn;
}
function genera_grid($aData = null, $aLabel = null, $sTitulo = 'Reporte', $iAncho = '400', $aAccion = null, $Totales = null, $aOrden = null)
{

	unset($arrayaDataGridVisible);
	unset($arrayaDataGridTipo);
	if ($sTitulo == 'DIRECTORIO') {

		$arrayaDataGridVisible[0] = 'S';
		$arrayaDataGridVisible[1] = 'S';
		$arrayaDataGridVisible[2] = 'S';
		$arrayaDataGridVisible[3] = 'S';
		$arrayaDataGridVisible[4] = 'S';
		$arrayaDataGridVisible[5] = 'S';
		$arrayaDataGridVisible[6] = 'S';
		$arrayaDataGridVisible[7] = 'S';
		$arrayaDataGridVisible[8] = 'S';
		$arrayaDataGridVisible[9] = 'S';
		$arrayaDataGridVisible[10] = 'S';
		$arrayaDataGridVisible[11] = 'S';
		$arrayaDataGridVisible[12] = 'N';
		$arrayaDataGridVisible[13] = 'S';
		$arrayaDataGridVisible[14] = 'S';

		$arrayaDataGridTipo[0] = 'N';		//Id
		$arrayaDataGridTipo[1] = 'T';		//Cliente
		$arrayaDataGridTipo[2] = 'T';		//SubCliente
		$arrayaDataGridTipo[3] = 'T';		//Tipo
		$arrayaDataGridTipo[4] = 'T';		//Factura
		$arrayaDataGridTipo[5] = 'T';		//Fec. Vence
		$arrayaDataGridTipo[6] = 'T';		//Detalle
		$arrayaDataGridTipo[7] = 'T';		//coti
		$arrayaDataGridTipo[8] = 'N';		//Debito Moneda Loc
		$arrayaDataGridTipo[9] = 'N';		//Credito Moneda Local
		$arrayaDataGridTipo[10] = 'N';		//Debito Moneda Ext
		$arrayaDataGridTipo[11] = 'N';		//Credito Moneda Ext
		$arrayaDataGridTipo[12] = 'I';		//modiricar
		$arrayaDataGridTipo[13] = 'I';		//eliminar
		$arrayaDataGridTipo[14] = 'N';		//DI

	} elseif ($sTitulo == 'DIARIO') {

		$arrayaDataGridVisible[0] = 'S';		// FILA
		$arrayaDataGridVisible[1] = 'S';		// CUENTA
		$arrayaDataGridVisible[2] = 'S';		// NOMBRE
		$arrayaDataGridVisible[3] = 'S';		// DOCUMENTO
		$arrayaDataGridVisible[4] = 'S';		// Cotizacion
		$arrayaDataGridVisible[5] = 'S';		// DEBITO LOCAL
		$arrayaDataGridVisible[6] = 'S';		// CREDITO LOCAL
		$arrayaDataGridVisible[7] = 'S';		// DEBITO EXT
		$arrayaDataGridVisible[8] = 'S';		// CREDITO EXT
		$arrayaDataGridVisible[9] = 'S';		// DETALLE
		$arrayaDataGridVisible[10] = 'S';		// MODIFICAR
		$arrayaDataGridVisible[11] = 'S';		// ELIMINAR
		$arrayaDataGridVisible[12] = 'S';		// CENTRO COSTO
		$arrayaDataGridVisible[13] = 'S';		// CENTRO ACTIVIDAD
		$arrayaDataGridVisible[14] = 'S';		// DIR
		$arrayaDataGridVisible[15] = 'S';		// RET

		$arrayaDataGridTipo[0] = 'N';
		$arrayaDataGridTipo[1] = 'T';
		$arrayaDataGridTipo[2] = 'T';
		$arrayaDataGridTipo[3] = 'T';
		$arrayaDataGridTipo[4] = 'T';
		$arrayaDataGridTipo[5] = 'N';
		$arrayaDataGridTipo[6] = 'N';
		$arrayaDataGridTipo[7] = 'N';
		$arrayaDataGridTipo[8] = 'N';
		$arrayaDataGridTipo[9] = 'T';
		$arrayaDataGridTipo[10] = 'I';
		$arrayaDataGridTipo[11] = 'I';
		$arrayaDataGridTipo[12] = 'I';
		$arrayaDataGridTipo[13] = 'I';
		$arrayaDataGridTipo[14] = 'N';
		$arrayaDataGridTipo[15] = 'N';
	} elseif ($sTitulo == 'RETENCION') {

		$arrayaDataGridVisible[0] = 'S';		//FILA
		$arrayaDataGridVisible[1] = 'S';		//Cta Ret
		$arrayaDataGridVisible[2] = 'S';		//Cliente
		$arrayaDataGridVisible[3] = 'S';		//Facura
		$arrayaDataGridVisible[4] = 'S';		//Ret Cliente
		$arrayaDataGridVisible[5] = 'S';		//Porc(%)
		$arrayaDataGridVisible[6] = 'S';		//Base Impo
		$arrayaDataGridVisible[7] = 'S';		//Valor
		$arrayaDataGridVisible[8] = 'S';		//N.- Retencion
		$arrayaDataGridVisible[9] = 'S';		//Detalle
		$arrayaDataGridVisible[10] = 'S';		//Origen
		$arrayaDataGridVisible[11] = 'S';		// COTIZACION
		$arrayaDataGridVisible[12] = 'S';		//DEBITO LOCAL
		$arrayaDataGridVisible[13] = 'S';		//CREDITO LOCAL
		$arrayaDataGridVisible[14] = 'S';		//DEBITO EXT
		$arrayaDataGridVisible[15] = 'S';		//CREDITO EXT
		$arrayaDataGridVisible[16] = 'N';		// MODIFICR
		$arrayaDataGridVisible[17] = 'S';		//ELIMINAR
		$arrayaDataGridVisible[18] = 'S';		//DI

		$arrayaDataGridTipo[0] = 'N';
		$arrayaDataGridTipo[1] = 'T';
		$arrayaDataGridTipo[2] = 'T';
		$arrayaDataGridTipo[3] = 'T';
		$arrayaDataGridTipo[4] = 'T';
		$arrayaDataGridTipo[5] = 'N';
		$arrayaDataGridTipo[6] = 'N';
		$arrayaDataGridTipo[7] = 'N';
		$arrayaDataGridTipo[8] = 'T';
		$arrayaDataGridTipo[9] = 'T';
		$arrayaDataGridTipo[10] = 'T';
		$arrayaDataGridTipo[11] = 'N';
		$arrayaDataGridTipo[12] = 'N';
		$arrayaDataGridTipo[13] = 'N';
		$arrayaDataGridTipo[14] = 'N';
		$arrayaDataGridTipo[15] = 'N';
		$arrayaDataGridTipo[16] = 'I';
		$arrayaDataGridTipo[17] = 'I';
		$arrayaDataGridTipo[18] = 'N';
	}
	


	//var_dump($aData);exit;
	if (is_array($aData) && is_array($aLabel)) {
		$iLabel = count($aLabel);
		$iData = count($aData);
		$sClass = 'on';
		$sHtml = '';
		$sHtml .= '<form id="DataGrid">';
		$sHtml .= '<table class="table table-striped table-condensed table-bordered" style="width: 100%" align="center">';
		$sHtml .= '<tr class="warning"><td colspan="' . $iLabel . '">Su consulta genero ' . $iData . ' registros de resultado</td></tr>';
		$sHtml .= '<tr>';

		// Genera Columnas de Grid
		for ($i = 0; $i < $iLabel; $i++) {
			$sLabel = explode('|', $aLabel[$i]);
			if ($sLabel[1] == '') {

				$aDataVisible = $arrayaDataGridVisible[$i];
				if ($aDataVisible == 'S') {
					$aDataVisible = '';
				} else {
					$aDataVisible = 'none;';
				}

				$sHtml .= '<td class="info" align="center" style="display: ' . $aDataVisible . '">' . $sLabel[0] . '</th>';
			} else {
				if ($sLabel[1] == $aOrden[0]) {
					if ($aOrden[1] == 'ASC') {
						$sLabel[1] .= '|DESC';
						$sImg = '<img src="' . $_COOKIE["JIREH_IMAGENES"] . 'iconos/ico_down.png" align="absmiddle" />';
					} else {
						$sLabel[1] .= '|ASC';
						$sImg = '<img src="' . $_COOKIE["JIREH_IMAGENES"] . 'iconos/ico_up.png" align="absmiddle" />';
					}
				} else {
					$sImg = '';
					$sLabel[1] .= '|ASC';
				}

				$sHtml .= '<th onClick="xajax_' . $sLabel[2] . '(xajax.getFormValues(\'form1\'),\'' . $sLabel[1] . '\')"
                                style="cursor: hand !important; cursor: pointer !important;" >' . $sLabel[0] . ' ';
				$sHtml .= $sImg;
				$sHtml .= '</td>';
			}
		}
		$sHtml .= '</tr>';


		for ($i = 0; $i < $iData; $i++) {
			if ($sClass == 'off')
				$sClass = 'on';
			else
				$sClass = 'off';

			$sHtml .= '<tr>';
			for ($j = 0; $j < $iLabel; $j++)
				if (is_float($aData[$i][$aLabel[$j]]))
					$sHtml .= '<td align="right">' . number_format($aData[$i][$aLabel[$j]], 2, ',', '.') . '</td>';
				else
					//				$sHtml .= '<td align="left">'.$aData[$i][$aLabel[$j]].'</td>';
					if ($j == 13 && $sTitulo != 'DIARIO') {
						$sHtml .= '<td align="left" style="display:none">' . $aData[$i][$aLabel[$j]] . '</td>';
					} else {
						$sHtml .= '<td align="left">' . $aData[$i][$aLabel[$j]] . '</td>';
					}
			$sHtml .= '</tr>';
		}

		//Totales
		$sHtml .= '<tr class="danger">';

		$total_debito_ad = 0;
		$total_credito_ad = 0;

		if (is_array($Totales)) {
			for ($i = 0; $i < $iLabel; $i++) {
				if ($i == 0)
					$sHtml .= '<td class="fecha_letra" align="right">TOTALES</td>';
				else {
					if ($Totales[$i] == '') {
						if ($Totales[$i] == '0.00') {
							$sHtml .= '<td align="right" class="fecha_letra">' . number_format($Totales[$i], 2, '.', ',') . '</td>';
						} else {
							$sHtml .= '<td align="right"></th>';
						}
					} else {
						$sHtml .= '<td align="right" class="fecha_letra">' . number_format($Totales[$i], 2, '.', ',') . '</td>';
					}
				}
			}
		}
		// Debito
		$total_debito_ad = number_format($Totales[5], 2, '.', ',');
		// Credito
		$total_credito_ad = number_format($Totales[6], 2, '.', ',');

		$sHtml .= '</tr>';

		//Saldos
		unset($_SESSION['ARRAY_SALDOS_TMP']);
		unset($arraySaldo);
		if ($sTitulo == 'DIARIO') {
			$sHtml .= '<tr class="danger">';
			$saldoDeb = 0;
			$saldoDeb_Ext = 0;
			$saldoCre = 0;
			$saldoCre_Ext = 0;
			if (is_array($Totales)) {
				$valDeb = 0;
				$valDeb_Ext = 0;
				$valCre = 0;
				$valCre_Ext = 0;
				for ($i = 0; $i < $iLabel; $i++) {
					if ($i == 5) {
						$total_deb = number_format($Totales[$i], 2, '.', ',');
						$valDeb += $total_deb;
					} elseif ($i == 6) {
						$total_deb = number_format($Totales[$i], 2, '.', ',');
						$valCre += $total_deb;
					} elseif ($i == 7) {
						$total_deb = number_format($Totales[$i], 2, '.', ',');
						$valDeb_Ext += $total_deb;
					} elseif ($i == 8) {
						$total_deb = number_format($Totales[$i], 2, '.', ',');
						$valCre_Ext += $total_deb;
					}
				} //fin for

				if ($valDeb > $valCre) {
					$saldoCre = $valCre - $valDeb;
					$saldoCre = number_format($saldoCre, 2, '.', ',');
					$arraySaldo[] = array('CR', $saldoCre);
				} elseif ($valDeb < $valCre) {
					$saldoDeb = $valDeb - $valCre;
					$saldoDeb = number_format($saldoDeb, 2, '.', ',');

					$arraySaldo[] = array('DB', $saldoDeb);
				}


				// MONDA Ext
				if ($valDeb_Ext > $valCre_Ext) {
					$saldoCre_Ext = $valCre_Ext - $valDeb_Ext;
					$saldoCre_Ext = number_format($saldoCre_Ext, 2, '.', ',');
				} elseif ($valDeb_Ext < $valCre_Ext) {
					$saldoDeb_Ext = $valDeb_Ext - $valCre_Ext;
					$saldoCre_Ext = number_format($saldoCre_Ext, 2, '.', ',');
				}

				// Control asiento Descuadrado
				// var_dump($Totales);
				// exit;
				$sHtml .= '<td style="display: none" class="fecha_letra" align="right"><input id="debito_total" name="debito_total" value="' . $total_debito_ad . '" /></td>';
				$sHtml .= '<td style="display: none" class="fecha_letra" align="right"><input id="credito_total" name="credito_total" value="' . $total_credito_ad . '" /></td>';


				$sHtml .= '<td class="fecha_letra" align="right">SALDO</td>';
				$sHtml .= '<td colspan="4"></td>';
				$sHtml .= '<td class="fecha_letra" align="right" >' . $saldoDeb . '</td>';
				$sHtml .= '<td class="fecha_letra" align="right" >' . $saldoCre . '</td>';
				$sHtml .= '<td class="fecha_letra" align="right" >' . $saldoDeb_Ext . '</td>';
				$sHtml .= '<td class="fecha_letra" align="right" >' . $saldoCre_Ext . '</td>';
				$sHtml .= '<td colspan="10"></td>';
			}
			$_SESSION['ARRAY_SALDOS_TMP'] = $arraySaldo;
			$sHtml .= '</tr>';
		}



		$sHtml .= '</table>';
		$sHtml .= '</form>';
	}
	return $sHtml;
}



/****************************************************************/
/* DF01 :: G E N E R A    F O R M U L A R I O    P R O C E S O  */
/****************************************************************/
function genera_formulario($sAccion = 'nuevo', $aForm = '', $idModulo = '197', $idClpv, $idCta, $codModu='')
{
	//  Definiciones
	global $DSN_Ifx, $DSN;
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$ifu = new Formulario;
	$ifu->DSN = $DSN_Ifx;

	$fu = new Formulario;
	$fu->DSN = $DSN;

	$oReturn      = new xajaxResponse();


	//variables de session
	unset($_SESSION['ARRAY_SALDOS_TMP']);
	$idempresa  = $_SESSION['U_EMPRESA'];
	$idsucursal = $_SESSION['U_SUCURSAL'];

	$empresa = $aForm['empresa'];
	$sucursal = $aForm['sucursal'];
	$modulo = 5;
	$tipoComp = 'DI';
	$diaHoy = date("Y-m-d");

	if (empty($empresa)) {
		$empresa = $idempresa;
	}

	if (empty($sucursal)) {
		$sucursal = $idsucursal;
	}

	//campo por defecto tran
	$sql = "select tidu_cod_tidu
			from saetidu where
			tidu_cod_empr = $empresa and
			tidu_cod_modu = $modulo and
			tidu_tip_tidu = '$tipoComp' and
			tidu_def_tidu = 'D'";
	$tipoDocDef = consulta_string($sql, 'tidu_cod_tidu', $oIfx, 0);

	//campo por defecto formato
	$sql = "select ftrn_cod_ftrn 
			from saeftrn where
			ftrn_cod_empr = $empresa and
			ftrn_cod_modu = $modulo and
			ftrn_tip_movi = '$tipoComp'";
	$tipoForDef = consulta_string($sql, 'ftrn_cod_ftrn', $oIfx, 0);



	//verificamos en el modulo se encuentra
	$sql = "SELECT * FROM comercial.menu_rd WHERE menu_id = '$idModulo'";
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			do {
				$padre = $oIfx->f('menu_codigo');
				$nombreMenu = $oIfx->f('menu_nombre');
			} while ($oIfx->SiguienteRegistro());
		}
	}
	//codigo de menu padre
	$padre = substr($padre, 0, 2);
	//tipo de documento
	$tidu_tip_tidu = strtoupper(substr($nombreMenu, 0, 2));
	$_SESSION['tidu_tip_tidu'] = $tidu_tip_tidu;
	//verificar cual es el modulo padre
	$sql = "SELECT * FROM comercial.menu_rd WHERE menu_codigo = '$padre'";
	$nombreModulo = consulta_string_func($sql, 'menu_nombre', $oIfx, '');
	$nombreModulo = strtoupper($nombreModulo);
	//codigo de modulo
	$sql = "SELECT * FROM saemodu WHERE upper(modu_des_modu) = '$nombreModulo'";
	$tidu_cod_modu = consulta_string_func($sql, 'modu_cod_modu', $oIfx, '');
	$_SESSION['tidu_cod_modu'] = $tidu_cod_modu;

	switch ($sAccion) {
		case 'nuevo':

			$ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "SELECT EMPR_COD_EMPR, EMPR_NOM_EMPR FROM SAEEMPR 
                                                                                where empr_cod_empr = $empresa ORDER BY 2", true, 200, 200);
			$ifu->AgregarComandoAlCambiarValor('empresa', 'cargar_sucu();');

			$ifu->AgregarCampoListaSQL(
				'sucursal',
				'Sucursal|left',
				"select sucu_cod_sucu, sucu_nom_sucu 
                                                                                    from saesucu where
                                                                                    sucu_cod_empr = $empresa order by 1 ",
				true,
				200,
				200
			);
			$ifu->AgregarComandoAlCambiarValor('sucursal', 'cargar_tran();');

			$ifu->AgregarCampoLista('tran_ret', 'Tipo|left', false, 200, 150);
			$ifu->AgregarComandoAlEscribir('tran_ret', 'replicar_valor();');


			$ifu->AgregarCampoListaSQL('tipo_doc', 'Tipo Documento|left', "select tidu_cod_tidu, 
                                                                                        tidu_des_tidu
                                                                                        from saetidu where
                                                                                        tidu_cod_empr = $empresa and
                                                                                        tidu_cod_modu = $tidu_cod_modu and
                                                                                        tidu_tip_tidu = '$tipoComp' ", true, 200, 150);

			$ifu->AgregarCampoFecha('fecha', 'Fecha|left', true, date('Y') . '/' . date('m') . '/' . date('d'));

			$ifu->AgregarCampoTexto('ruc', 'Ruc|left', true, '', 100, 120);

			$ifu->AgregarCampoTexto('cliente_nombre', 'Beneficiario|left', true, '', 400, 200);
			$ifu->AgregarComandoAlEscribir('cliente_nombre', 'autocompletar(' . $empresa . ', event, 0 );form1.cliente_nombre.value=form1.cliente_nombre.value.toUpperCase();');

			$ifu->AgregarCampoTexto('cliente', 'Cliente|left', true, '', 50, 50);
			$ifu->AgregarComandoAlPonerEnfoque('cliente', 'this.blur()');

			$ifu->AgregarCampoListaSQL('empleado', 'Cobrador|left', "select cobr_cod_cobr, (cobr_ape_cobr ||' '|| cobr_nom_cobr)
																				from saecobr
																				where cobr_cod_empr = $empresa", false, 200, 200);

			$ifu->AgregarCampoTexto('valor', 'Valor|left', true, 0, 80, 150);
			$ifu->AgregarComandoAlPonerEnfoque('valor', 'this.blur()');

			$ifu->AgregarCampoListaSQL('formato', 'Formato|left', "select ftrn_cod_ftrn, ftrn_des_ftrn 
                                                                                    from saeftrn where
                                                                                    ftrn_cod_empr = $empresa and
                                                                                    ftrn_cod_modu = $modulo and
                                                                                    ftrn_tip_movi = '$tipoComp' ", true, 200, 150);
			$ifu->AgregarCampoLista('deas', 'Deas|left', false, 200, 200);
			$sql = "select deas_cod_deas,  saedeas.deas_des_deas from saedeas order by 2";
			if ($oIfx->Query($sql)) {
				if ($oIfx->NumFilas() > 0) {
					do {
						$ifu->AgregarOpcionCampoLista('deas', $oIfx->f('deas_cod_deas') . ' ' . $oIfx->f('deas_des_deas'), $oIfx->f('deas_cod_deas'));
					} while ($oIfx->SiguienteRegistro());
				}
			}
			$oIfx->Free();

			$ifu->AgregarCampoTexto('detalle', 'Detalle|left', true, '', 350, 200);
			$ifu->AgregarComandoAlEscribir('detalle', 'cargar_detalle();');

			$ifu->AgregarCampoTexto('asto_cod', 'Asiento|left', false, '', 150, 120);
			$ifu->AgregarComandoAlPonerEnfoque('asto_cod', 'this.blur()');

			$ifu->AgregarCampoTexto('compr_cod', 'Comprobante N.-|left', false, '', 150, 120);
			$ifu->AgregarComandoAlPonerEnfoque('compr_cod', 'this.blur()');

			unset($_SESSION['aDataGirdDir']);
			$oReturn->assign("divDir", "innerHTML", "");

			unset($_SESSION['aDataGirdDiar']);
			$oReturn->assign("divDiario", "innerHTML", "");

			unset($_SESSION['aDataGirdRet']);
			$oReturn->assign("divRet", "innerHTML", "");

			// DIRECTORIO
			$ifu->AgregarCampoTexto('clpv_nom', 'Cliente - Proveedor|left', false, '', 200, 200);
			$ifu->AgregarComandoAlEscribir('clpv_nom', 'autocompletar(' . $empresa . ', event, 1 );form1.clpv_nom.value=form1.clpv_nom.value.toUpperCase();');

			$ifu->AgregarCampoTexto('clpv_cod', 'Cliente|left', false, '', 50, 50);
			$ifu->AgregarComandoAlPonerEnfoque('clpv_cod', 'this.blur()');

			$ifu->AgregarCampoLista('tran', 'Tipo|left', false, 200, 150);

			$ifu->AgregarCampoLista('ccli', 'SubCliente|left', false, 200, 150);

			$ifu->AgregarCampoTexto('factura', 'Factura|left', false, '', 130, 200);
			$ifu->AgregarComandoAlEscribir('factura', 'facturas(' . $empresa . ', event );');

			$ifu->AgregarCampoTexto('fact_valor', 'Valor|left', false, 0, 100, 100);
			$ifu->AgregarComandoAlCambiarValor('fact_valor', 'mascara(this,cpf)');
			$ifu->AgregarComandoAlEscribir('fact_valor', 'enter_dir(event);');

			$ifu->AgregarCampoTexto('det_dir', 'Detalle|left', false, '', 200, 100);

			// RETENCION
			$ifu->AgregarCampoTexto('cod_ret', 'Cta Ret.|left', false, '', 130, 200);
			$ifu->AgregarComandoAlEscribir('cod_ret', 'cod_retencion(' . $empresa . ', event );');

			$ifu->AgregarCampoTexto('fact_ret', 'Factura|left', false, '', 130, 200);
			$ifu->AgregarComandoAlEscribir('fact_ret', 'fact_retencion(' . $empresa . ', event ); replicar_valor();');
		
			$ifu->AgregarCampoTexto('ret_clpv', 'Ret. Cliente|left', false, '', 60, 200);

			$ifu->AgregarCampoNumerico('ret_porc', 'Porc.(%)|left', false, '', 60, 50);
			$ifu->AgregarComandoAlEscribir('ret_porc', 'calculaValorRetenido();');

			$ifu->AgregarCampoNumerico('ret_base', 'Base Impo.|left', false, '', 100, 200);
			$ifu->AgregarComandoAlEscribir('ret_base', 'calculaValorRetenido();');

			$ifu->AgregarCampoTexto('ret_val', 'Valor|left', false, '', 100, 200);
			$ifu->AgregarComandoAlEscribir('ret_val', 'mascara(this,cpf)');

			$ifu->AgregarCampoTexto('valor_retenido', 'Valor|left', false, '', 100, 200);
			$ifu->AgregarComandoAlEscribir('valor_retenido', 'mascara(this,cpf); replicar_valor();');

			$ifu->AgregarCampoNumerico('ret_num', 'N.- Retencion|left', false, '', 100, 200);
			$ifu->AgregarComandoAlCambiarValor('ret_num', 'numero_ret(1);');

			$ifu->AgregarCampoTexto('ret_det', 'Detalle|left', false, '', 200, 200);
			$ifu->AgregarComandoAlEscribir('ret_det', 'replicar_valor();');


			$ifu->AgregarCampoTexto('cta_deb', 'Debito|left', false, '', 50, 200);

			$ifu->AgregarCampoTexto('cta_cre', 'Credito|left', false, '', 50, 200);

			$ifu->AgregarCampoTexto('tipo', 'tipo|left', false, '', 50, 200);

			$ifu->AgregarCampoFecha('origen', 'Origen|left', true, date('Y') . '/' . date('m') . '/' . date('d'));

			// CUENTAS DASI
			$ifu->AgregarCampoOculto('cod_cta', '');

			$ifu->AgregarCampoTexto('nom_cta', 'Nombre Cta|left', false, '', 200, 200);
			$ifu->AgregarComandoAlEscribir('nom_cta', 'auto_dasi(' . $empresa . ', event, 0 );form1.nom_cta.value=form1.nom_cta.value.toUpperCase();');

			$ifu->AgregarCampoTexto('documento', 'Documento|left', false, '', 100, 10);
			$ifu->AgregarComandoAlCambiarValor('documento', 'numero_ret(2);');

			$ifu->AgregarCampoNumerico('val_cta', 'Valor|left', false, '', 100, 200);
			$ifu->AgregarComandoAlCambiarValor('val_cta', 'mascara(this,cpf)');
			$ifu->AgregarComandoAlEscribir('val_cta', 'enter_dasi(event);');


			$ifu->AgregarCampoLista('crdb', 'Tipo|left', false, '90');
			$ifu->AgregarOpcionCampoLista('crdb', 'CREDITO', 'CR');
			$ifu->AgregarOpcionCampoLista('crdb', 'DEBITO', 'DB');

			//ASIENTO CONTABLE
			$fu->AgregarCampoTexto('ejer_cod', 'Ejericio|left', false, '', 100, 100);
			$fu->AgregarCampoTexto('prdo_cod', 'Periodo|left', false, '', 100, 100);

			$ifu->AgregarCampoOculto('tipoClpv', '');

			$ifu->cCampos["empresa"]->xValor = $empresa;
			$ifu->cCampos["sucursal"]->xValor = $sucursal;
			$ifu->cCampos["tipo_doc"]->xValor = $tipoDocDef;
			$ifu->cCampos["formato"]->xValor = $tipoForDef;

			$ifu->AgregarCampoTexto('detalla_diario', 'Detalle|left', false, '', 170, 200);
			$ifu->AgregarCampoNumerico('cotizacion', 'Cotizacion|left', false, 1, 80, 100);
			$ifu->AgregarCampoNumerico('cotizacion_ext', 'Cotizacion Ext.|left', false, 1, 80, 100);


			// MONEDA
			$ifu->AgregarCampoListaSQL('moneda', 'Moneda|left', "select mone_cod_mone, mone_des_mone  from saemone where mone_cod_empr = $idempresa ", true, 200, 150);
			$ifu->AgregarComandoAlCambiarValor('moneda', 'cargar_coti();');

			$sql      = "select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa ";
			$mone_cod = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');
			$ifu->cCampos["moneda"]->xValor = $mone_cod;

			// COTIZACION MONEDA EXTRANJERA
			$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');
			$fech = date('Y-m-d');

			$sql = "select tcam_val_tcam from saetcam where
									mone_cod_empr = $idempresa and
									tcam_cod_mone = $mone_extr and
									tcam_fec_tcam in(
														select max(tcam_fec_tcam)  from saetcam where
																mone_cod_empr = $idempresa and
																tcam_cod_mone = $mone_extr and tcam_fec_tcam<='$fech'
													)";

			$coti = consulta_string($sql, 'tcam_val_tcam', $oIfx, 0);
			$ifu->cCampos["cotizacion_ext"]->xValor = $coti;



			// CENTRO COSTOS 
			$ifu->AgregarCampoListaSQL('ccosn', 'Centro Costo|left', "select ccosn_cod_ccosn, ccosn_nom_ccosn || ' - ' || ccosn_cod_ccosn from saeccosn where
																						ccosn_cod_empr  = $idempresa and
																						ccosn_mov_ccosn = 1
																						order by 1 ", false, 150, 150);

			// CENTRO DE ACTIVIDAD
			$ifu->AgregarCampoListaSQL('actividad', 'Centro Actividad|left', "select cact_cod_cact , cact_nom_cact ||  ' - ' || cact_cod_cact from saecact where
																								cact_cod_empr = $idempresa order by 2 ", false, 150, 150);

			//campo por defecto formato
			$sql = "select ftrn_cod_ftrn 
								from saeftrn where
								ftrn_cod_empr = $idempresa and
								ftrn_cod_modu = $modulo and
								ftrn_tip_movi = '$tipoComp'";
			$tipoForDef = consulta_string($sql, 'ftrn_cod_ftrn', $oIfx, 0);
			$ifu->cCampos["formato"]->xValor = $tipoForDef;

			$ifu->AgregarCampoCheck('clpv_empl', 'Empleado S/N', false, 'N');

			break;
	}

	$sHtml .= '<table class="table table-striped table-condensed" style="margin-bottom: 0px; width: 100%" align="center">
					<tr>
						<td>
							<div class="btn btn-primary btn-sm" onclick="genera_formulario();">
                                <span class="glyphicon glyphicon-file"></span>
                                Nuevo
                            </div>
							<div class="btn btn-primary btn-sm" onclick="guardar();"  id = "guardar">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                Guardar
                            </div>
							<div class="btn btn-primary btn-sm" onclick="vista_previa();">
                                <span class="glyphicon glyphicon-print"></span>
                                Imprimir
                            </div>

							<div class="btn btn-primary btn-sm" onclick="abre_modal_adjuntos();">
										<span class="glyphicon glyphicon-random"></span>
										Adjuntos
							</div>
							
						</td>
						<td align="right">
							<div class="btn btn-danger btn-sm" onclick="cancelar_pedido();">
                                <span class="glyphicon glyphicon-remove"></span>
                                Cancelar
                            </div>
						</td>
					</tr>
                  </table>';
	$sHtml .= '<table class="table table-striped table-condensed" style="margin-bottom: 0px; width: 100%" align="center">';
	$sHtml .= '<tr>
                       <td class="bg-primary" align="center" colspan="6">INGRESO DE DIARIO</td>
                   </tr>';

	$sHtml .= '<tr class="bg-info">
						<td></td>
						<td></td>
						<td align="left" colspan="3" style="font-size: 12px;">
								<span> Asiento Contable: ' . $ifu->ObjetoHtml('asto_cod') . '</span>
								<span> Comprobante: ' . $ifu->ObjetoHtml('compr_cod') . '</span>
						</td>
						<td align="right" class="fecha_letra">Fecha Registro Contable  <input type="date" name="fecha" id="fecha" step="1" value="' . $diaHoy . '" onchange="controlPeriodoIfx()"></td>
					</tr>';
	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('empresa') . '</td>
						<td>' . $ifu->ObjetoHtml('empresa') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('sucursal') . '</td>
						<td>' . $ifu->ObjetoHtml('sucursal') . '</td>     
						<td>' . $ifu->ObjetoHtmlLBL('tipo_doc') . '</td>
						<td>' . $ifu->ObjetoHtml('tipo_doc') . '</td>
						
					</tr>';
	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('cliente_nombre') . '</td>
						<td>
							' . $ifu->ObjetoHtml('cliente_nombre') . '
							Empleado S/N:
							' . $ifu->ObjetoHtml('clpv_empl') . '
						</td>
						<td>' . $ifu->ObjetoHtmlLBL('empleado') . '</td>
						<td>' . $ifu->ObjetoHtml('empleado') . '</td>    
						<td>' . $ifu->ObjetoHtmlLBL('deas') . '</td>
						<td>' . $ifu->ObjetoHtml('deas') . '</td>
                   </tr>';
	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('detalle') . '</td>
						<td>' . $ifu->ObjetoHtml('detalle') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('moneda') . '</td>
						<td>' . $ifu->ObjetoHtml('moneda') . '</td> 
						<td >' . $ifu->ObjetoHtmlLBL('cotizacion') . '</td>			
						<td >' . $ifu->ObjetoHtml('cotizacion') . '</td>			
						   
                   </tr>';
	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('valor') . '</td>
						<td>
								<table>
									<tr>
											<td>' . $ifu->ObjetoHtml('valor') . '</td>
											
											<td></td>
											<td>
													<div class="btn btn-success btn-sm" onclick="prestamo();">
														<span class="glyphicon glyphicon-plus"></span>
														Prestamo Empleado
													</div>
											</td>
											
									</tr>
								</table>							
						</td>
						<td>' . $ifu->ObjetoHtmlLBL('formato') . '</td>
						<td>' . $ifu->ObjetoHtml('formato') . '</td>  
						<td >' . $ifu->ObjetoHtmlLBL('cotizacion_ext') . '</td>			
						<td >' . $ifu->ObjetoHtml('cotizacion_ext') . '</td>
                   </tr>';
	$sHtml .= '<tr style="display:none">
                            <td class="labelFrm">' . $ifu->ObjetoHtml('cliente') . '</td>
                   </tr>';
	$sHtml .= '<tr style="display:none">
                        <td class="labelFrm" >' . $fu->ObjetoHtml('ejer_cod') . '</td>
                        <td class="labelFrm" >' . $fu->ObjetoHtml('prdo_cod') . '</td>
                   </tr>';
	$sHtml .= '<tr>
						<td height="25px" colspan="6"></td>
				   </tr>';
	$sHtml .= '</table>';


	// DIRECTORIO
	$sHtml_dir .= '<table class="table table-bordered table-hover" align="left" cellpadding="0" cellspacing="2" width="100%" border="0">';
	$sHtml_dir .= '<tr>
						<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('clpv_nom') . '</td>
						<td bgcolor="#F2F2F2" style="display:none"></td>   
						<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('ccli') . '</td>
						<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('tran') . '</td>
						<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('det_dir') . '</td>   
								
						<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('factura') . '</td>
						<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('fact_valor') . '</td>                                       
						<td bgcolor="#F2F2F2" align="center"></td>
					</tr>';

	$sHtml_dir .= '<tr>
						<td>' . $ifu->ObjetoHtml('clpv_nom') . '</td>
						<td style="display:none">' . $ifu->ObjetoHtml('clpv_cod') . '' . $ifu->ObjetoHtml('tipoClpv') . '</td> 
						<td>' . $ifu->ObjetoHtml('ccli') . '</td>
						<td>' . $ifu->ObjetoHtml('tran') . '</td>
						<td>' . $ifu->ObjetoHtml('det_dir') . '</td>    	
						<td>' . $ifu->ObjetoHtml('factura') . '</td>
						<td>' . $ifu->ObjetoHtml('fact_valor') . '</td>                                        
						<td align="center">
							<div class="btn btn-success btn-sm" onclick="anadir_dir();">
								<span class="glyphicon glyphicon-plus"></span>
								Agregar
							</div>
						</td>
					</tr>';


	$sHtml_dir .= '</table>';

	// RETENCION
	$sHtml_ret .= '<table class="table table-bordered table-hover" style="margin-bottom: 0px; width: 100%" align="center">';
	$sHtml_ret .= '<tr>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('cod_ret') . '   </td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('fact_ret') . '  </td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('ret_clpv') . '  </td>    
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('ret_porc') . '  </td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('ret_base') . '  </td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('valor_retenido') . '</td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('ret_num') . '   </td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('ret_det') . '   </td>
							<td bgcolor="#F2F2F2">Tipo Ret</td>
							<td bgcolor="#F2F2F2">Serie</td>
							<td bgcolor="#F2F2F2">No. Autorizacion</td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('origen') . '   </td>
							<td bgcolor="#F2F2F2" style="display:none"></td>
							<td bgcolor="#F2F2F2" style="display:none"></td>
							<td bgcolor="#F2F2F2" style="display:none"></td>
							<td bgcolor="#F2F2F2" align="center"></td>
						</tr>';

	$sHtml_ret .= '<tr>
							<td>' . $ifu->ObjetoHtml('cod_ret') . '     </td> 
							<td>' . $ifu->ObjetoHtml('fact_ret') . '    </td>
							<td>' . $ifu->ObjetoHtml('ret_clpv') . '    </td>    
							<td>' . $ifu->ObjetoHtml('ret_porc') . '    </td>
							<td>' . $ifu->ObjetoHtml('ret_base') . '    </td>
							<td>' . $ifu->ObjetoHtml('valor_retenido') . '</td>
							<td>' . $ifu->ObjetoHtml('ret_num') . '    </td>
							<td>' . $ifu->ObjetoHtml('ret_det') . '    </td>
							<td>' . $ifu->ObjetoHtml('tran_ret') . '    </td>
							<td><input type="text" id="serie_ret_sj" maxlength="6"  name="serie_ret_sj" onchange="replicar_valor();"/></td>
							<td><input type="text" id="numero_autorizacion" name="numero_autorizacion" onchange="replicar_valor();"/></td>
							<td>' . $ifu->ObjetoHtml('origen') . '     </td>
							<td style="display:none">' . $ifu->ObjetoHtml('cta_deb') . '</td>
							<td style="display:none">' . $ifu->ObjetoHtml('cta_cre') . '</td>
							<td style="display:none">' . $ifu->ObjetoHtml('tipo') . '</td>
							<td align="center">
								<div class="btn btn-success btn-sm" onclick="anadir_ret();">
									<span class="glyphicon glyphicon-plus"></span>
									Agregar
								</div>
							</td>
						</tr>';


	$sHtml_ret .= '</table>';

	// CUENTA DASI
	$sHtml_dasi .= '<table class="table table-bordered table-hover" style="margin-bottom: 0px; width: 100%" align="center">';
	$sHtml_dasi .= '<tr>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('nom_cta') . '</td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('documento') . '</td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('detalla_diario') . '</td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('crdb') . '</td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('ccosn') . '</td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('actividad') . '</td>
							<td bgcolor="#F2F2F2">' . $ifu->ObjetoHtmlLBL('val_cta') . '</td>
							<td bgcolor="#F2F2F2" align="center"></td>
						</tr>';

	$sHtml_dasi .= '<tr>
							<td>' . $ifu->ObjetoHtml('nom_cta') . '' . $ifu->ObjetoHtml('cod_cta') . '</td>
							<td>' . $ifu->ObjetoHtml('documento') . '</td> 
							<td>' . $ifu->ObjetoHtml('detalla_diario') . '</td>
							<td>' . $ifu->ObjetoHtml('crdb') . '</td> 
							<td>' . $ifu->ObjetoHtml('ccosn') . '</td>
							<td>' . $ifu->ObjetoHtml('actividad') . '</td>
							<td>' . $ifu->ObjetoHtml('val_cta') . '</td> 	
							<td align="center">
									<div id="btn_dis_ccos" class="btn btn-primary btn-sm" onclick="anadir_dasi_distri();">
										<span class="glyphicon glyphicon-th-list"></span>
										Distribuir
									</div>
								</td>							
							<td align="center">
								<div class="btn btn-success btn-sm" onclick="anadir_dasi();">
									<span class="glyphicon glyphicon-plus"></span>
									Agregar
								</div>
							</td>
						</tr>';

	$sHtml_dasi .= '</table>';


	$oReturn->assign("divFormularioCabecera", "innerHTML", $sHtml);
	$oReturn->assign("divFormDir", "innerHTML", $sHtml_dir);
	$oReturn->assign("divFormRet", "innerHTML", $sHtml_ret);
	$oReturn->assign("divFormDiario", "innerHTML", $sHtml_dasi);
	$oReturn->assign("cliente_nombre", "placeholder", "DIGITE CLIENTE Y PRESIONE ENTER PARA BUSCAR");
	$oReturn->assign("clpv_nom", " placeholder", "PRESIONE ENTER PARA BUSCAR");
	$oReturn->assign("factura", "placeholder", "ENTER PARA BUSCAR");
	$oReturn->assign("cod_ret", "placeholder", "ENTER PARA BUSCAR");
	$oReturn->assign("fact_ret", "placeholder", "ENTER PARA BUSCAR");
	$oReturn->assign("cod_cta", "placeholder", "ENTER PARA BUSCAR");
	$oReturn->assign("nom_cta", "placeholder", "ENTER PARA BUSCAR");
	$oReturn->assign("cliente_nombre", "focus()", "");



	$importacion_modulos = $_SESSION['GESTION_IMPORTACION'];
	if (!empty($importacion_modulos)) {
		$sql_saeinfimp = "SELECT * from saeinfimp where infimp_cod_infimp = $importacion_modulos ";
		$infimp_cod_clpv = consulta_string($sql_saeinfimp, 'infimp_cod_clpv', $oIfx, 0);
		$infimp_fact_prove = consulta_string($sql_saeinfimp, 'infimp_fact_prove', $oIfx, 0);
		$infimp_val_infimp = consulta_string($sql_saeinfimp, 'infimp_val_infimp', $oIfx, 0);
		$infimp_val_iva = consulta_string($sql_saeinfimp, 'infimp_val_iva', $oIfx, 0);



		$sql_clpv = "SELECT c.clpv_cod_clpv, c.clpv_nom_clpv,  c.clpv_ruc_clpv,
						c.clpv_cod_vend, c.clpv_cot_clpv, c.clpv_pre_ven, '' as direccion, 
						'' as telefono, clpv_etu_clpv, clpv_cod_tpago, clpv_cod_fpagop, clpv_pro_pago,
						c.clpv_clopv_clpv
						from saeclpv c  where 
						c.clpv_cod_empr       = $idempresa and
						--     c.clpv_clopv_clpv     = 'PV' and
						clpv_cod_clpv = $infimp_cod_clpv
						group by 1,2,3,4,5,6, 9, 10, 11, 12, 13 order by 2 LIMIT 50";

		$clpv_cod_clpv = consulta_string($sql_clpv, 'clpv_cod_clpv', $oIfx, '');
		$clpv_nom_clpv = consulta_string($sql_clpv, 'clpv_nom_clpv', $oIfx, '');
		$clpv_ruc_clpv = consulta_string($sql_clpv, 'clpv_ruc_clpv', $oIfx, '');
		$direccion = consulta_string($sql_clpv, 'direccion', $oIfx, '');
		$telefono = consulta_string($sql_clpv, 'telefono', $oIfx, '');
		$clpv_cod_vend = consulta_string($sql_clpv, 'clpv_cod_vend', $oIfx, '');
		$clpv_cot_clpv = consulta_string($sql_clpv, 'clpv_cot_clpv', $oIfx, '');
		$clpv_pre_ven = consulta_string($sql_clpv, 'clpv_pre_ven', $oIfx, '');
		$clpv_cod_fpagop = consulta_string($sql_clpv, 'clpv_cod_fpagop', $oIfx, '');
		$clpv_cod_tpago = consulta_string($sql_clpv, 'clpv_cod_tpago', $oIfx, '');
		$clpv_pro_pago = consulta_string($sql_clpv, 'clpv_pro_pago', $oIfx, '');
		$clpv_etu_clpv = consulta_string($sql_clpv, 'clpv_etu_clpv', $oIfx, '');
		$clpv_clopv_clpv = consulta_string($sql_clpv, 'clpv_clopv_clpv', $oIfx, '');

		$oReturn->assign("cliente", "value", $clpv_cod_clpv);
		$oReturn->assign("cliente_nombre", "value", $clpv_nom_clpv);
		$oReturn->assign("clpv_cod", "value", $clpv_cod_clpv);
		$oReturn->assign("clpv_nom", "value", $clpv_nom_clpv);

		$oReturn->script('cargar_lista_tran(\'' . $clpv_clopv_clpv . '\');');
		$oReturn->script('cargar_lista_subcliente();');

		$oReturn->assign("tipoClpv", "value", $clpv_clopv_clpv);
		$oReturn->assign("tipo_doc", "value", '021');
		$oReturn->assign("detalle", "value", 'IMPORTACION');





		// ----------------------------------------------------------------------------------
		// Diario
		// ----------------------------------------------------------------------------------

		$sql_parametro_tran = "SELECT pccp_cod_facp FROM saepccp where pccp_cod_empr = $idempresa";
		$pccp_cod_facp = consulta_string($sql_parametro_tran, 'pccp_cod_facp', $oIfx, '');

		$oReturn->assign("tran", "value", $pccp_cod_facp);


		// ----------------------------------------------------------------------------------
		// FIN Diario
		// ----------------------------------------------------------------------------------




		// ----------------------------------------------------------------------------------
		// Directorio
		// ----------------------------------------------------------------------------------

		$sql_parametro_cuentas = "SELECT parm_cod_cuen, parm_cod_cuen2 FROM saeparm where parm_cod_empr = $idempresa";
		$parm_cod_cuen = consulta_string($sql_parametro_cuentas, 'parm_cod_cuen', $oIfx, '');
		$parm_cod_cuen2 = consulta_string($sql_parametro_cuentas, 'parm_cod_cuen2', $oIfx, '');


		// ----------------------------------------------------------------------------------
		// FIN Directorio
		// ----------------------------------------------------------------------------------





	}

	//VALIDA SI RECIBE PARAMETROS DEL MODULO CHEQUES PROTESTADOS

	if (!empty($idClpv)) {

		//DATOS DEL CLEINTE

		$sqlcl = "select clpv_nom_clpv, clpv_clopv_clpv from saeclpv where clpv_cod_clpv=$idClpv";
		$nombre = consulta_string($sqlcl, 'clpv_nom_clpv', $oIfx, '');
		$tipo = consulta_string($sqlcl, 'clpv_clopv_clpv', $oIfx, '');

		//DATOS DE LA CUENTA
		$sqlct = "select cuen_cod_cuen, cuen_nom_cuen, cuen_nom_ingl, cuen_mov_cuen, cuen_bie_cuen, cuen_cact_cuen, cuen_ccos_cuen
		from saecuen where cuen_cod_cuen='$idCta'";
		$cta_nom = consulta_string($sqlct, 'cuen_nom_cuen', $oIfx, '');
		$cuen_ccos = consulta_string($sqlct, 'cuen_ccos_cuen', $oIfx, '');
		$cuen_cact = consulta_string($sqlct, 'cuen_cact_cuen', $oIfx, '');

		$oReturn->script("datos_clpv($idClpv,'$nombre','$tipo','$idCta','$cta_nom','$cuen_ccos','$cuen_cact')");
	}

	if(!empty($codModu)){
		$oReturn->script("anadir_dasi_asi_inic()");
	}


	return $oReturn;
}



function replicar_valor($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $cod_ret = $aForm['cod_ret'];

    $sql = "SELECT tran_cod_tran from saetran where tran_cod_tret='$cod_ret'";
	$codigo_transaccion=consulta_string($sql,'tran_cod_tran',$oIfx,'');
    $oReturn->assign("tran", "value", $codigo_transaccion);

    return $oReturn;
}




// FACTURAS
function reporte_facturas($idempresa, $idsucursal, $clpv_cod, $factura, $tran_clpv, $det, $tipo, $ccli = 0)
{
	//Definiciones
	global $DSN_Ifx;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$ifu = new Formulario;
	$ifu->DSN = $DSN;

	$oReturn = new xajaxResponse();
	unset($_SESSION['U_FACTURA']);
	$event='';
	if ($tipo == 'PV') {
		$event='style="display:none"';
	}

	// CUENTAS DE CLPV
	$sql = "select clpv_cod_cuen, grpv_cod_grpv from saeclpv where clpv_cod_empr = $idempresa and clpv_cod_clpv = $clpv_cod ";
	$clpv_cuen = consulta_string_func($sql, 'clpv_cod_cuen', $oIfx, '');
	if (empty($clpv_cuen)) {
		$clpv_gr = consulta_string_func($sql, 'grpv_cod_grpv', $oIfx, '');
		$sql = "select  grpv_cta_grpv  from saegrpv where
                        grpv_cod_empr = $idempresa and
                        grpv_cod_grpv = '$clpv_gr' ";
		$clpv_cuen = consulta_string_func($sql, 'grpv_cta_grpv', $oIfx, '');
	}

	// NOMBRE CUENtA
	$sql = "select cuen_nom_cuen from saecuen where 
                    cuen_cod_empr = $idempresa and
                    cuen_cod_cuen = '$clpv_cuen' ";
	$cuen_nom = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

	$table_op .= '<br>';
	$table_op .= '<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:98%;">';
	$table_op .= '<legend class="Titulo">FACTURAS</legend>';
	$table_op .= '<table align="center" border="0" cellpadding="2" cellspacing="1" width="99%" class="footable">';
	$table_op .= '<tr>
                            <th class="diagrama" colspan="5"></th>
                            <th class="diagrama" colspan="4" align="right">
                                <input type="button" value="ACEPTAR"
                                    onClick="javascript: cargar();"
                                    id="BuscaBtn" class="myButton_BT"
                                    style="width:80px; height:25px;" />
                            </th>
                     </tr>';
	$table_op .= '<tr>
                            <th class="diagrama">N.-</th>
                            <th class="diagrama">Tran</th>
                            <th class="diagrama">Factura</th>
                            <th class="diagrama">F. Emision</th>
                            <th class="diagrama">F. Vence</th>                            
                            <th class="diagrama" '.$event.'>Valor Rete.</th>
                            <th class="diagrama">Saldo</th>
							<th class="diagrama">Valor</th>
                            <th class="diagrama">
								<input type="checkbox" name="check" value="S" onchange="marcar(this);">
							</th>
                     </tr>';

	if ($tipo == 'CL') {
		/*$sql_sp = "SELECT
					dmcc_num_fac AS dmcp_num_fac,
					MIN ( dmcc_fec_emis ) AS dcmp_fec_emis,
					MAX ( dmcc_fec_ven ) AS dmcp_fec_ven,
					MAX ( dmcc_cod_mone ) AS dmcp_cod_mone,
					MAX ( dmcc_cod_ejer ) AS dmcp_cod_ejer,
					SUM ( dmcc_deb_ml ) as dcmp_deb_ml,
					SUM ( dmcc_deb_mext ) as dmcp_deb_mext,
					MAX (
						(
							SELECT SUM
								( COALESCE ( dmcc_deb_ml, 0 ) - COALESCE ( dmcc_cre_ml, 0 ) ) 
							FROM
								saedmcc y 
							WHERE
								y.dmcc_cod_empr = saedmcc.dmcc_cod_empr 
								AND y.clpv_cod_clpv = saedmcc.clpv_cod_clpv 
								AND y.dmcc_num_fac = saedmcc.dmcc_num_fac 
							) 
						) saldo,
						MAX (
							(
							SELECT SUM
								( COALESCE ( dmcc_deb_mext, 0 ) - COALESCE ( dmcc_cre_mext, 0 ) ) 
							FROM
								saedmcc y 
							WHERE
								y.dmcc_cod_empr = saedmcc.dmcc_cod_empr 
								AND y.clpv_cod_clpv = saedmcc.clpv_cod_clpv 
								AND y.dmcc_num_fac = saedmcc.dmcc_num_fac 
							) 
						) ld_saldo_mext,
						MAX (
							(
							SELECT COALESCE
								( SUM ( dmcc_cre_ml ), 0 ) 
							FROM
								saedmcc x,
								saetret,
								saetran 
							WHERE
								x.dmcc_num_fac = saedmcc.dmcc_num_fac 
								AND x.dmcc_cod_empr = saedmcc.dmcc_cod_empr 
								AND x.clpv_cod_clpv = saedmcc.clpv_cod_clpv 
								AND saetran.tran_cod_tran = x.dmcc_cod_tran 
								AND saetran.tran_cod_modu = x.dmcc_cod_modu 
								AND saetran.tran_cod_empr = x.dmcc_cod_empr 
								AND saetret.tret_cod = saetran.tran_cod_tret 
								AND saetret.tret_cod_empr = saetran.tran_cod_empr 
							) 
						) ret 
					FROM
						saedmcc 
					WHERE
						dmcc_cod_empr = $idempresa 
						AND clpv_cod_clpv = $clpv_cod 
						AND dmcc_est_dmcc <> 'AN' 
					GROUP BY
						1 
					HAVING
						MAX (
							(
							SELECT SUM
								( COALESCE ( dmcc_deb_ml, 0 ) - COALESCE ( dmcc_cre_ml, 0 ) ) 
							FROM
								saedmcc y 
							WHERE
								y.dmcc_cod_empr = saedmcc.dmcc_cod_empr 
								AND y.clpv_cod_clpv = saedmcc.clpv_cod_clpv 
								AND y.dmcc_num_fac = saedmcc.dmcc_num_fac 
							) 
						) <> 0 
					ORDER BY dcmp_fec_emis
		
			";*/

		$sql_sp = "SELECT
			dmcc_num_fac AS dmcp_num_fac,
			MIN ( dmcc_fec_emis ) AS dcmp_fec_emis,
			MAX ( dmcc_fec_ven ) AS dmcp_fec_ven,
			MAX ( dmcc_cod_mone ) AS dmcp_cod_mone,
			MAX ( dmcc_cod_ejer ) AS dmcp_cod_ejer,
			SUM ( dmcc_deb_ml ) AS dcmp_deb_ml,
			SUM ( dmcc_deb_mext ) AS dmcp_deb_mext,
			SUM ( COALESCE ( dmcc_deb_ml, 0 ) - COALESCE ( dmcc_cre_ml, 0 )) saldo,
			SUM ( COALESCE ( dmcc_deb_mext, 0 ) - COALESCE ( dmcc_cre_mext, 0 )) ld_saldo_mext,
			MAX (
				(
				SELECT COALESCE
					( SUM ( dmcc_cre_ml ), 0 ) 
				FROM
					saedmcc x,
					saetret,
					saetran 
				WHERE
					x.dmcc_num_fac = saedmcc.dmcc_num_fac 
					AND x.dmcc_cod_empr = saedmcc.dmcc_cod_empr 
					AND x.clpv_cod_clpv = saedmcc.clpv_cod_clpv 
					AND saetran.tran_cod_tran = x.dmcc_cod_tran 
					AND saetran.tran_cod_modu = x.dmcc_cod_modu 
					AND saetran.tran_cod_empr = x.dmcc_cod_empr 
					AND saetret.tret_cod = saetran.tran_cod_tret 
					AND saetret.tret_cod_empr = saetran.tran_cod_empr 
				) 
			) ret 
		FROM
			saedmcc 
		WHERE
			dmcc_cod_empr =  $idempresa
			AND clpv_cod_clpv = $clpv_cod 
			AND dmcc_est_dmcc <> 'AN' 
		GROUP BY
			1 
		HAVING
			SUM ( COALESCE ( dmcc_deb_ml, 0 ) - COALESCE ( dmcc_cre_ml, 0 ) )  <> 0 
		ORDER BY
			dcmp_fec_emis";
		//echo $sql_sp;exit;

	} elseif ($tipo == 'PV') {
		//$sql_sp = "execute  procedure sp_consulta_factprove_web( $idempresa, $idsucursal , $clpv_cod, '$factura')";
		// TRANSACCIONAL
		$sql = "select trim(para_fac_cxp) as tran  from saepara where para_cod_empr = $idempresa and para_cod_sucu = $idsucursal ";
		$tran_cod = consulta_string_func($sql, 'tran', $oIfx, '');

		//PENDIENTE OPTIMIZAR LA CONSULTA PARA TRAER EL VALOR DE LAS RETENCIONES

		$sql_sp = "SELECT
                dmcp_num_fac,
				MAX(dmcp_cod_tran) AS dmcp_cod_tran,
                MIN ( dcmp_fec_emis ) AS dcmp_fec_emis,
                MAX ( dmcp_fec_ven ) AS dmcp_fec_ven,
                MAX ( dmcp_cod_mone ) AS dmcp_cod_mone,
                MAX ( dmcp_cod_ejer ) AS dmcp_cod_ejer,
                SUM ( dcmp_deb_ml ),
                SUM ( dmcp_deb_mext ),
				SUM ( COALESCE ( dcmp_deb_ml, 0 ) - COALESCE ( dcmp_cre_ml, 0 ) ) as saldo,
                SUM ( COALESCE ( dmcp_deb_mext, 0 ) - COALESCE ( dmcp_cre_mext, 0 ) ) as saldo_mext
                FROM
                    saedmcp 
                WHERE
                    dmcp_cod_empr = $idempresa 
                    AND clpv_cod_clpv = '$clpv_cod' 
                    AND dmcp_est_dcmp <> 'AN' 
                    -- AND dmcp_num_fac LIKE '%0180316%'
                GROUP BY
                    1
                HAVING
                    SUM ( COALESCE ( dcmp_deb_ml, 0 ) - COALESCE ( dcmp_cre_ml, 0 ) ) <> 0 
                ORDER BY
                    dcmp_fec_emis";
	}
	//$oReturn->alert($sql_sp);

	//CONSULTA SQL COMPROBANTE BASE



	unset($array);
	$total   = 0;
	$i       = 1;
	$tot_ret = 0;
	if ($oIfx->Query($sql_sp)) {
		if ($oIfx->NumFilas() > 0) {
			do {
				$fact_num = $oIfx->f('dmcp_num_fac');
				$fec_emis = fecha_mysql_func($oIfx->f('dcmp_fec_emis'));
				$fec_venc = fecha_mysql_func($oIfx->f('dmcp_fec_ven'));
				$saldo    = $oIfx->f('saldo');
				$saldo_condicion    = $oIfx->f('saldo');
				//echo $saldo;exit;
				$ret      = $oIfx->f('ret');

				$tran = $oIfx->f('dmcp_cod_tran');

				$ifu->AgregarCampoTexto($i, '', false, 0, 100, 80);
				$ifu->AgregarComandoAlCambiarValor($i, "xajax_calculo( xajax.getFormValues('form1')) ");
				$ifu->AgregarComandoAlEscribir($i, 'mascara(this,cpf)');

				$array[$i] = array(
					$i,         $fact_num,      $fec_emis,      $fec_venc,
					$saldo,     $idempresa,     $idsucursal,    $clpv_cod,
					$tran_clpv, $det,           $clpv_cuen,     $cuen_nom,
					$tipo, $ccli
				);

				if ($tran == 'ANT') {
					$color = 'style="background-color: yellow"';
				} else {
					$color = 'style="background-color: "';
				}

				if ($sClass == 'off') $sClass = 'on';
				else $sClass = 'off';
				$table_op .= '<tr height="20" class="' . $sClass . '"
                                            onMouseOver="javascript:this.className=\'link\';"
                                            onMouseOut="javascript:this.className=\'' . $sClass . '\';" ' . $color . '>';
				$table_op .= '<td align="left">' . $i . '</td>';
				$table_op .= '<td align="left">' . $tran . '</td>';
				$table_op .= '<td align="left">' . $fact_num . '</td>';
				$table_op .= '<td align="left">' . $fec_emis . '</td>';
				$table_op .= '<td align="left">' . $fec_venc . '</td>';
				if ($tipo == 'CL') {
				$table_op .= '<td align="right">' . number_format($ret, 2, '.', ',') . '</td>';
				}
				$table_op .= '<td align="right">' . number_format($saldo, 2, '.', ',') . '</td>';
				$table_op .= '<td align="right">' . $ifu->ObjetoHtml($i) . '</td>';

				$table_op .= '<td align="center">
										<input type="checkbox" name="check_' . $i . '" id="check_' . $i . '" value="S" onclick="pintaValor(' . $i . ', \'' . $saldo . '\')">
									</td>';
				$table_op .= '</tr>';
				$i++;
				$total   += $saldo;
				$tot_ret += $ret;
			} while ($oIfx->SiguienteRegistro());
			$table_op .= '<tr height="20" class="' . $sClass . '"
                                            onMouseOver="javascript:this.className=\'link\';"
                                            onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="right" class="fecha_letra">TOTAL:</td>';
			if ($tipo == 'CL') {
			$table_op .= '<td align="right" class="fecha_letra">' . number_format($tot_ret, 2, '.', ',') . '</td>';
			}
			$table_op .= '<td align="right" class="fecha_letra">' . number_format($total, 2, '.', ',') . '</td>';
			$table_op .= '<td align="right" class="letra_rojo" id="tot_cobro">0.00</td>';
			$table_op .= '<td align="right"></td>';
			$table_op .= '</tr>';
		} else {
			$table_op = '<span class="fecha_letra">Sin Datos...</span>';
		}
	}
	$oIfx->Free();
	$table_op .= '</table></fieldset>';

	$_SESSION['U_FACTURA'] = $array;

	$oReturn->assign("divFormularioDetalle", "innerHTML", $table_op);

	return $oReturn;
}


// CALCULO
function calculo($aForm)
{
	//Definiciones
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oReturn = new xajaxResponse();
	$array = $_SESSION['U_FACTURA'];

	if (count($array) > 0) {
		$total = 0;
		foreach ($array as $val) {
			$id     = $val[0];
			$txt    = abs(str_replace(",", "", $aForm[$id]));
			$total += $txt;
		}
	}
	$oReturn->assign("tot_cobro", "innerHTML", number_format(round($total, 2), 2, '.', ','));

	return $oReturn;
}

function cargar_tot($aForm)
{
	//Definiciones
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oReturn = new xajaxResponse();
	$array = $_SESSION['U_FACTURA'];
	$check = $aForm['check'];

	if (count($array) > 0) {
		$total = 0;

		if (!empty($check)) {
			foreach ($array as $val) {
				$id    = $val[0];
				$valor = $val[4];

				$oReturn->assign($id, "value", $valor);
				$total += $valor;
			}
		} else {
			foreach ($array as $val) {
				$id    = $val[0];
				$oReturn->assign($id, "value", 0);
			}
		}
	}
	$oReturn->assign("tot_cobro", "innerHTML", $total);

	return $oReturn;
}


// FACTURAS
function reporte_facturas_ret($idempresa, $idsucursal, $clpv_cod, $factura)
{
	//Definiciones
	global $DSN_Ifx;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oIfxA = new Dbo;
	$oIfxA->DSN = $DSN_Ifx;
	$oIfxA->Conectar();

	$ifu = new Formulario;
	$ifu->DSN = $DSN;

	$oReturn = new xajaxResponse();

	$table_op .= '<fieldset style="border:#999999 1px solid; padding:2px; text-align:center; width:98%;">';
	$table_op .= '<legend class="Titulo">FACTURAS</legend>';
	$table_op .= '<table align="center" border="0" cellpadding="2" cellspacing="1" width="99%" class="footable">';
	$table_op .= '<tr>
                            <th class="diagrama">N.-</th>
                            <th class="diagrama">Tran</th>
                            <th class="diagrama">Factura</th>
                            <th class="diagrama">F. Emision</th>
                            <th class="diagrama">F. Vence</th>
                            <th class="diagrama">Saldo</th>
                            <th class="diagrama">Valor Rete.</th>
                     </tr>';

	$sql_sp = "SELECT * FROM  sp_consulta_fact_web( $idempresa, $idsucursal , $clpv_cod, '$factura' )";
	unset($array);
	$total   = 0;
	$i       = 1;
	$tot_ret = 0;
	if ($oIfx->Query($sql_sp)) {
		if ($oIfx->NumFilas() > 0) {
			do {
				$tran     = $oIfx->f('tran');
				$fact_num = $oIfx->f('factura');
				$fec_emis = fecha_mysql_funcYmd($oIfx->f('fec_emis'));
				$fec_venc = fecha_mysql_funcYmd($oIfx->f('fec_ven'));
				$saldo    = $oIfx->f('saldo_local');
				$ret      = $oIfx->f('ret');

				// BASE IMPONIBLE
				list($a, $b, $c) = explode('-', $fact_num);
				$sql = "select  ( fact_con_miva + fact_sin_miva + fact_fle_fact  +  fact_otr_fact + fact_fin_fact ) as total  
                                    from saefact where
                                    fact_cod_clpv   = $clpv_cod and
                                    fact_num_preimp = '$b' and
                                    fact_cod_empr   = $idempresa and 
                                    fact_cod_sucu = $idsucursal ";
				$base = consulta_string_func($sql, 'total', $oIfxA, 0);

				if ($sClass == 'off') $sClass = 'on';
				else $sClass = 'off';
				$table_op .= '<tr height="20" class="' . $sClass . '"
									onMouseOver = "javascript:this.className=\'link\';"
									onMouseOut  = "javascript:this.className=\'' . $sClass . '\';" 
									onclick     = "cargar_fact(\'' . $fact_num . '\', \'' . $base . '\');"
									style="cursor: pointer;">';
				$table_op .= '<td align="right">' . $i . '</td>';
				$table_op .= '<td align="right">' . $tran . '</td>';
				$table_op .= '<td align="right">' . $fact_num . '</td>';
				$table_op .= '<td align="right">' . $fec_emis . '</td>';
				$table_op .= '<td align="right">' . $fec_venc . '</td>';
				$table_op .= '<td align="right">' . $saldo . '</td>';
				$table_op .= '<td align="right">' . $ret . '</td>';
				$table_op .= '</tr>';
				$i++;
				$total   += $saldo;
				$tot_ret += $ret;
			} while ($oIfx->SiguienteRegistro());
			$table_op .= '<tr height="20" class="' . $sClass . '"
                                            onMouseOver="javascript:this.className=\'link\';"
                                            onMouseOut="javascript:this.className=\'' . $sClass . '\';"
                                            onclick = "cargar_fact(\'' . $fact_num . '\');" >';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="right" class="fecha_letra">TOTAL:</td>';
			$table_op .= '<td align="right" class="fecha_letra">' . $total . '</td>';
			$table_op .= '<td align="right" class="fecha_letra">' . $tot_ret . '</td>';
			$table_op .= '</tr>';
		} else {
			$table_op = '<span class="fecha_letra">Sin Datos...</span>';
		}
	}
	$oIfx->Free();
	$table_op .= '</table></fieldset>';

	$oReturn->assign("divFormularioDetalle", "innerHTML", $table_op);

	return $oReturn;
}



// DIRECTORIO
function agrega_modifica_grid_dir($nTipo = 0, $aForm = '', $id = '', $idempresa = '', $coti = '', $mone_cod = '', $coti_ext = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();



	try {



		$aDataGrid = $_SESSION['aDataGirdDir'];
		$aDataDiar = $_SESSION['aDataGirdDiar'];
		$array     = $_SESSION['U_FACTURA'];

		$aLabelGrid = array(
			'Id', 				  'Cliente', 			'SubCliente', 			'Tipo', 					'Factura',
			'Fec. Vence', 		  'Detalle', 			'Cotizacion', 			'Debito Moneda Local', 		'Credito Moneda Local',
			'Debito Moneda Ext',  'Credito Moneda Ext', 'Modificar', 			'Eliminar',					'DI'
		);

		$aLabelDiar = array(
			'Fila', 			  'Cuenta', 			'Nombre', 				'Documento', 				'Cotizacion',
			'Debito Moneda Local', 'Credito Moneda Local', 'Debito Moneda Ext', 	'Credito Moneda Ext', 		'Detalle',
			'Modificar', 		  'Eliminar', 			'Centro Costo', 		'Centro Actividad',
			'DIR',				  'RET'
		);


		$oReturn = new xajaxResponse();

		// VARIABLES
		$tran_cod = $aForm["tran"];
		$detalle  = $aForm["det_dir"];
		$doc      = $aForm["documento"];

		if (empty($idempresa)) {
			$idempresa = $aForm["empresa"];
		}

		if (empty($mone_cod)) {
			$mone_cod = $aForm["moneda"];
		}

		$sql      = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
		$mone_base = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

		if ($mone_cod == $mone_base) {
			$sql      = "select pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa ";
			$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

			$sql = "select tcam_val_tcam from saetcam where
				mone_cod_empr = $idempresa and
				tcam_cod_mone = $mone_extr and
				tcam_fec_tcam in (
									select max(tcam_fec_tcam)  from saetcam where
											mone_cod_empr = $idempresa and
											tcam_cod_mone = $mone_extr
								)  ";
			$coti = $coti_ext;
		}

		if ($nTipo == 0) {
			if (count($array) > 0) {
				$cont = 0;
				$contd = 0;
				foreach ($array as $val) {
					$ix        = $val[0];
					$fact_num  = $val[1];
					$fec_emis  = $val[2];
					$fec_venc  = $val[3];
					$saldo     = $val[4];
					$idempresa = $val[5];
					$idsucursal = $val[6];
					$clpv_cod  = $val[7];
					$tran_cod  = $val[8];
					$det_dir   = $val[9];
					$clpv_cuen = $val[10];
					$cuen_nom  = $val[11];
					$tipo  	   = $val[12];
					$ccli  	   = $val[13];


					if ($tipo == 'PV') {
						$mensaje_cuenta_error  = "Cuenta contable: $clpv_cuen no existe. Parametrizar en Lado derecho/ Cuentas por Pagar/ Maestros de Transaccion. Transaccion: $tran_cod";
					} else if ($tipo == 'CL') {
						$mensaje_cuenta_error  = "Cuenta contable: $clpv_cuen no existe. Parametrizar en Lado derecho/ Cuentas por Cobrar/ Maestros de Transaccion. Transaccion: $tran_cod";
					}
					if (empty($cuen_nom)) {
						throw new Exception($mensaje_cuenta_error);
					}

					$txt  = str_replace(",", "", $aForm[$ix]);
					$txt  = abs($txt);
					if ($txt > 0) {

						$valDirCre = 0;
						$valDiaCre = 0;
						$valDirDeb = 0;
						$valDiaDeb = 0;

						if ($tipo == 'CL') {
							$modulo = 3;
						} elseif ($tipo == 'PV') {
							$modulo = 4;
						}

						//tipo de transaccion
						$sql = "select trans_tip_tran from saetran where 
										tran_cod_tran = '$tran_cod' and 
										tran_cod_modu = $modulo and 
										tran_cod_empr = $idempresa ";
						$trans_tip_tran = consulta_string($sql, 'trans_tip_tran', $oIfx, '');

						$sql = "select tran_cod_cuen from saetran where 
								  tran_cod_tran = '$tran_cod' and 
								  tran_cod_modu = $modulo and
								  tran_cod_empr = $idempresa and
								( tran_ant_tran = 1 or 
								  tran_cie_tran = 1   
								) ";
						$clpv_cuen_tmp = consulta_string_func($sql, 'tran_cod_cuen', $oIfx, '');
						//$oReturn->alert($clpv_cuen_tmp);
						if (!empty($clpv_cuen_tmp)) {
							$clpv_cuen = $clpv_cuen_tmp;
							$sql = "select cuen_nom_cuen from saecuen where cuen_cod_cuen = '$clpv_cuen' and cuen_cod_empr = $idempresa ";
							$cuen_nom = consulta_string($sql, 'cuen_nom_cuen', $oIfx, '');
							if ($tipo == 'PV') {
								$mensaje_cuenta_error  = "Cuenta contable: $clpv_cuen no existe. Parametrizar en Lado derecho/ Cuentas por Pagar/ Maestros de Transaccion. Transaccion: $tran_cod";
							} else if ($tipo == 'CL') {
								$mensaje_cuenta_error  = "Cuenta contable: $clpv_cuen no existe. Parametrizar en Lado derecho/ Cuentas por Cobrar/ Maestros de Transaccion. Transaccion: $tran_cod";
							}
							if (empty($cuen_nom)) {
								throw new Exception($mensaje_cuenta_error);
							}
						}

						//$oReturn->alert($sql);

						if ($trans_tip_tran == 'DB') {
							$valDirDeb = $txt;
							$valDiaDeb = $txt;
						} elseif ($trans_tip_tran == 'CR') {
							$valDirCre = $txt;
							$valDiaCre = $txt;
						}

						// DIRECTORIO
						$cont = count($aDataGrid);
						$aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
						$aDataGrid[$cont][$aLabelGrid[1]] = $clpv_cod;
						$aDataGrid[$cont][$aLabelGrid[2]] = $ccli;
						$aDataGrid[$cont][$aLabelGrid[3]] = $tran_cod;
						$aDataGrid[$cont][$aLabelGrid[4]] = $fact_num;
						$aDataGrid[$cont][$aLabelGrid[5]] = $fec_venc;
						$aDataGrid[$cont][$aLabelGrid[6]] = $det_dir;
						$aDataGrid[$cont][$aLabelGrid[7]] = $coti;

						if ($mone_cod == $mone_base) {
							// moneda local
							$cre_tmp = 0;
							$deb_tmp = 0;
							if ($coti > 0) {
								$cre_tmp = round(($valDirCre / $coti), 2);
							}

							if ($coti > 0) {
								$deb_tmp = round(($valDirDeb / $coti), 2);
							}

							$aDataGrid[$cont][$aLabelGrid[8]] = $valDirDeb;
							$aDataGrid[$cont][$aLabelGrid[9]] = $valDirCre;
							$aDataGrid[$cont][$aLabelGrid[10]] = $deb_tmp;
							$aDataGrid[$cont][$aLabelGrid[11]] = $cre_tmp;
						} else {
							// moneda extra

							$aDataGrid[$cont][$aLabelGrid[8]] = $valDirDeb * $coti;
							$aDataGrid[$cont][$aLabelGrid[9]] = $valDirCre * $coti;

							$aDataGrid[$cont][$aLabelGrid[10]] = $valDirDeb;
							$aDataGrid[$cont][$aLabelGrid[11]] = $valDirCre;
						}

						$aDataGrid[$cont][$aLabelGrid[12]] = '';
						$contd = count($aDataDiar);
						$aDataGrid[$cont][$aLabelGrid[13]] = '<div align="center">
																<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																title = "Presione aqui para Eliminar"
																style="cursor: hand !important; cursor: pointer !important;"
																onclick="javascript:xajax_elimina_detalle_dir(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
																alt="Eliminar"
																align="bottom" />
															</div>';
						$aDataGrid[$cont][$aLabelGrid[14]] = $contd;

						// DIARIO
						$aDataDiar[$contd][$aLabelDiar[0]] = floatval($contd);
						$aDataDiar[$contd][$aLabelDiar[1]] = $clpv_cuen;
						$aDataDiar[$contd][$aLabelDiar[2]] = $cuen_nom;
						$aDataDiar[$contd][$aLabelDiar[3]] = $doc;
						$aDataDiar[$contd][$aLabelDiar[4]] = $coti;


						if ($mone_cod == $mone_base) {
							// moneda local
							$cre_tmp = 0;
							$deb_tmp = 0;
							if ($coti > 0) {
								$cre_tmp = round(($valDirCre / $coti), 2);
							}

							if ($coti > 0) {
								$deb_tmp = round(($valDirDeb / $coti), 2);
							}

							$aDataDiar[$contd][$aLabelDiar[5]] = $valDiaDeb;
							$aDataDiar[$contd][$aLabelDiar[6]] = $valDiaCre;
							$aDataDiar[$contd][$aLabelDiar[7]] = $deb_tmp;
							$aDataDiar[$contd][$aLabelDiar[8]] = $cre_tmp;
						} else {
							// moneda extra
							$aDataDiar[$contd][$aLabelDiar[5]] = $valDiaDeb * $coti;
							$aDataDiar[$contd][$aLabelDiar[6]] = $valDiaCre * $coti;
							$aDataDiar[$contd][$aLabelDiar[7]] = $valDirDeb;
							$aDataDiar[$contd][$aLabelDiar[8]] = $valDirCre;
						}


						$aDataDiar[$contd][$aLabelDiar[9]] = $det_dir;
						$aDataDiar[$contd][$aLabelDiar[10]] = '';
						$aDataDiar[$contd][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																	title = "Presione aqui para Eliminar"
																	style="cursor: hand !important; cursor: pointer !important;"
																	onclick="javascript:xajax_elimina_detalle_dir(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
																	alt="Eliminar"
																	align="bottom" />';
						$aDataDiar[$contd][$aLabelDiar[12]] = '';
						$aDataDiar[$contd][$aLabelDiar[13]] = '';
						$aDataDiar[$contd][$aLabelDiar[14]] = $cont;
					}
				} // fin foreach
			} // fin if

		}

		$_SESSION['aDataGirdDir'] = $aDataGrid;
		$sHtml = mostrar_grid_dir($idempresa, $idsucursal);
		$oReturn->assign("divDir", "innerHTML", $sHtml);

		// DIARIO
		$sHtml = '';
		$_SESSION['aDataGirdDiar'] = $aDataDiar;
		$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
		$oReturn->assign("divDiario", "innerHTML", $sHtml);

		// TOTAL DIARIO
		$oReturn->script("total_diario();");
		$oReturn->script("cerrar_ventana();");
		$oReturn->assign('tran', 'value', 0);
		$oReturn->assign('ccli', 'value', 0);
	} catch (Exception $e) {
		$oReturn->alert($e->getMessage());
	}

	return $oReturn;
}

// DIRECTRIO SIN FACTURA
function agrega_modifica_grid_dir_ori($nTipo = 0, $aForm = '', $id = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$aDataGrid = $_SESSION['aDataGirdDir'];
	$aDataDiar = $_SESSION['aDataGirdDiar'];

	$aLabelGrid = array(
		'Id', 'Cliente', 'SubCliente', 'Tipo', 'Factura', 'Fec. Vence', 'Detalle', 'Cotizacion',
		'Debito Moneda Local', 'Credito Moneda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 'Modificar', 'Eliminar',		'DI'
	);

	$aLabelDiar = array(
		'Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar',
		'Centro Costo', 			'Centro Actividad',	'DIR',		'RET'
	);

	$oReturn = new xajaxResponse();

	// VARIABLES
	$tran_cod 	= $aForm["tran"];
	$detalle  	= $aForm["det_dir"];
	$doc      	= $aForm["documento"];
	$idsucursal = $aForm["sucursal"];

	if (empty($idempresa)) {
		$idempresa = $aForm["empresa"];
	}

	if (empty($mone_cod)) {
		$mone_cod = $aForm["moneda"];
	}

	$sql      = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
	$mone_base = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

	if ($mone_cod == $mone_base) {
		$sql      = "select pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa ";
		$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

		$sql = "select tcam_val_tcam from saetcam where
				mone_cod_empr = $idempresa and
				tcam_cod_mone = $mone_extr and
				tcam_fec_tcam in (
									select max(tcam_fec_tcam)  from saetcam where
											mone_cod_empr = $idempresa and
											tcam_cod_mone = $mone_extr
								)  ";

		$coti = $aForm["cotizacion_ext"];
	} else {
		$coti = $aForm["cotizacion"];
	}

	if ($nTipo == 0) {
		$cont = 0;
		$contd = 0;
		$ix        = $val[0];
		$fact_num  = $aForm['factura'];
		$fec_emis  = $aForm['fecha'];
		$fec_venc  = $aForm['fecha'];
		$clpv_cod  = $aForm['clpv_cod'];
		$tran_cod  = $aForm['tran'];
		$det_dir   = $aForm['det_dir'];

		// CUENTAS DE CLPV
		$sql       = "select clpv_cod_cuen, grpv_cod_grpv, clpv_clopv_clpv from saeclpv 
										where clpv_cod_empr = $idempresa and 
										clpv_cod_clpv 		= $clpv_cod ";
		$tipo      = consulta_string_func($sql, 'clpv_clopv_clpv', $oIfx, '');

		if ($tipo == 'CL') {
			$modulo = 3;
		} elseif ($tipo == 'PV') {
			$modulo = 4;
		}

		$sql = "select tran_cod_cuen from saetran where 
								  tran_cod_tran = '$tran_cod' and 
								  tran_cod_modu = $modulo and
								  tran_cod_sucu = $idsucursal and
								  tran_cod_empr = $idempresa and
								( tran_ant_tran = 1 or 
								  tran_cie_tran = 1   
								) ";
		$clpv_cuen = consulta_string_func($sql, 'tran_cod_cuen', $oIfx, '');

		if (empty($clpv_cuen)) {
			$sql       = "select clpv_cod_cuen, grpv_cod_grpv, clpv_clopv_clpv from saeclpv 
										where clpv_cod_empr = $idempresa and 
										clpv_cod_clpv 		= $clpv_cod ";
			$clpv_cuen = consulta_string_func($sql, 'clpv_cod_cuen', $oIfx, '');
			if (empty($clpv_cuen)) {
				$clpv_gr = consulta_string_func($sql, 'grpv_cod_grpv', $oIfx, '');
				$sql = "select  grpv_cta_grpv  from saegrpv where
										grpv_cod_empr = $idempresa and
										grpv_cod_grpv = '$clpv_gr' ";
				$clpv_cuen = consulta_string_func($sql, 'grpv_cta_grpv', $oIfx, '');
			}
		}

		// NOMBRE CUENtA
		$sql = "select cuen_nom_cuen from saecuen where 
			                    cuen_cod_empr = $idempresa and
			                    cuen_cod_cuen = '$clpv_cuen' ";
		$cuen_nom = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

		$ccli  	   = $aForm['ccli'];
		$txt  		= abs(str_replace(",", "", $aForm['fact_valor']));


		$valDirCre = 0;
		$valDiaCre = 0;
		$valDirDeb = 0;
		$valDiaDeb = 0;



		//tipo de transaccion
		$sql = "select trans_tip_tran from saetran where tran_cod_tran = '$tran_cod' and tran_cod_modu = $modulo and tran_cod_empr = $idempresa ";
		$trans_tip_tran = consulta_string($sql, 'trans_tip_tran', $oIfx, '');

		//$oReturn->alert($sql);

		if ($trans_tip_tran == 'DB') {
			$valDirDeb = $txt;
			$valDiaDeb = $txt;
		} elseif ($trans_tip_tran == 'CR') {
			$valDirCre = $txt;
			$valDiaCre = $txt;
		}

		// DIRECTORIO
		$cont = count($aDataGrid);
		$aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
		$aDataGrid[$cont][$aLabelGrid[1]] = $clpv_cod;
		$aDataGrid[$cont][$aLabelGrid[2]] = $ccli;
		$aDataGrid[$cont][$aLabelGrid[3]] = $tran_cod;
		$aDataGrid[$cont][$aLabelGrid[4]] = $fact_num;
		$aDataGrid[$cont][$aLabelGrid[5]] = $fec_venc;
		$aDataGrid[$cont][$aLabelGrid[6]] = $det_dir;
		$aDataGrid[$cont][$aLabelGrid[7]] = $coti;

		if ($mone_cod == $mone_base) {
			// moneda local
			$cre_tmp = 0;
			$deb_tmp = 0;
			if ($coti > 0) {
				$cre_tmp = round(($valDirCre / $coti), 2);
			}

			if ($coti > 0) {
				$deb_tmp = round(($valDirDeb / $coti), 2);
			}

			$aDataGrid[$cont][$aLabelGrid[8]] = $valDirDeb;
			$aDataGrid[$cont][$aLabelGrid[9]] = $valDirCre;
			$aDataGrid[$cont][$aLabelGrid[10]] = $deb_tmp;
			$aDataGrid[$cont][$aLabelGrid[11]] = $cre_tmp;
		} else {
			// moneda extra

			$aDataGrid[$cont][$aLabelGrid[8]] = $valDirDeb * $coti;
			$aDataGrid[$cont][$aLabelGrid[9]] = $valDirCre * $coti;

			$aDataGrid[$cont][$aLabelGrid[10]] = $valDirDeb;
			$aDataGrid[$cont][$aLabelGrid[11]] = $valDirCre;
		}

		$aDataGrid[$cont][$aLabelGrid[12]] = '';
		$contd = count($aDataDiar);
		$aDataGrid[$cont][$aLabelGrid[13]] = '<div align="center">
																<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																title = "Presione aqui para Eliminar"
																style="cursor: hand !important; cursor: pointer !important;"
																onclick="javascript:xajax_elimina_detalle_dir(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
																alt="Eliminar"
																align="bottom" />
															</div>';
		$aDataGrid[$cont][$aLabelGrid[14]] = $contd;

		// DIARIO
		//echo $coti;exit;
		$aDataDiar[$contd][$aLabelDiar[0]] = floatval($contd);
		$aDataDiar[$contd][$aLabelDiar[1]] = $clpv_cuen;
		$aDataDiar[$contd][$aLabelDiar[2]] = $cuen_nom;
		$aDataDiar[$contd][$aLabelDiar[3]] = $doc;
		$aDataDiar[$contd][$aLabelDiar[4]] = $coti;

		if ($mone_cod == $mone_base) {
			// moneda local
			$cre_tmp = 0;
			$deb_tmp = 0;
			if ($coti > 0) {
				$cre_tmp = round(($valDirCre / $coti), 2);
			}

			if ($coti > 0) {
				$deb_tmp = round(($valDirDeb / $coti), 2);
			}

			$aDataDiar[$contd][$aLabelDiar[5]] = $valDiaDeb;
			$aDataDiar[$contd][$aLabelDiar[6]] = $valDiaCre;
			$aDataDiar[$contd][$aLabelDiar[7]] = $deb_tmp;
			$aDataDiar[$contd][$aLabelDiar[8]] = $cre_tmp;
		} else {
			// moneda extra
			$aDataDiar[$contd][$aLabelDiar[5]] = $valDiaDeb * $coti;
			$aDataDiar[$contd][$aLabelDiar[6]] = $valDiaCre * $coti;
			$aDataDiar[$contd][$aLabelDiar[7]] = $valDirDeb;
			$aDataDiar[$contd][$aLabelDiar[8]] = $valDirCre;
		}


		$aDataDiar[$contd][$aLabelDiar[9]] = $det_dir;
		$aDataDiar[$contd][$aLabelDiar[10]] = '';
		$aDataDiar[$contd][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																		title = "Presione aqui para Eliminar"
																		style="cursor: hand !important; cursor: pointer !important;"
																		onclick="javascript:xajax_elimina_detalle_dir(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
																		alt="Eliminar"
																		align="bottom" />';
		$aDataDiar[$contd][$aLabelDiar[12]] = '';
		$aDataDiar[$contd][$aLabelDiar[13]] = '';
		$aDataDiar[$contd][$aLabelDiar[14]] = $cont;
	}

	$_SESSION['aDataGirdDir'] = $aDataGrid;
	$sHtml = mostrar_grid_dir($idempresa, $idsucursal);
	$oReturn->assign("divDir", "innerHTML", $sHtml);

	// DIARIO
	$sHtml = '';
	$_SESSION['aDataGirdDiar'] = $aDataDiar;
	$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
	$oReturn->assign("divDiario", "innerHTML", $sHtml);

	//$oReturn->alert($sHtml);




	// TOTAL DIARIO
	$oReturn->script("total_diario();");
	$oReturn->script("cerrar_ventana();");
	$oReturn->assign('tran', 'value', 0);
	$oReturn->assign('ccli', 'value', 0);


	$importacion_modulos = $_SESSION['GESTION_IMPORTACION'];
	if (!empty($importacion_modulos)) {
		$sql_saeinfimp = "SELECT * from saeinfimp where infimp_cod_infimp = $importacion_modulos ";
		$infimp_cod_clpv = consulta_string($sql_saeinfimp, 'infimp_cod_clpv', $oIfx, 0);
		$infimp_fact_prove = consulta_string($sql_saeinfimp, 'infimp_fact_prove', $oIfx, 0);
		$infimp_val_infimp = consulta_string($sql_saeinfimp, 'infimp_val_infimp', $oIfx, 0);
		$infimp_val_iva = consulta_string($sql_saeinfimp, 'infimp_val_iva', $oIfx, 0);
		$infimp_cod_import = consulta_string($sql_saeinfimp, 'infimp_cod_import', $oIfx, 0);

		// ----------------------------------------------------------------------------------
		// Directorio
		// ----------------------------------------------------------------------------------

		$sql_parametro_cuentas = "SELECT parm_cod_cuen, parm_cod_cuen2 FROM saeparm where parm_cod_empr = $idempresa";
		$parm_cod_cuen = consulta_string($sql_parametro_cuentas, 'parm_cod_cuen', $oIfx, '');
		$parm_cod_cuen2 = consulta_string($sql_parametro_cuentas, 'parm_cod_cuen2', $oIfx, '');



		// cuenta uno
		$sql_cuentas = "select cuen_cod_cuen, cuen_nom_cuen, cuen_nom_ingl, cuen_mov_cuen, cuen_bie_cuen, cuen_cact_cuen, cuen_ccos_cuen
				from saecuen where
				cuen_cod_empr = $idempresa and
				cuen_cod_cuen = '$parm_cod_cuen'
				order by cuen_cod_cuen ";
		$cuen_cod_cuen = consulta_string($sql_cuentas, 'cuen_cod_cuen', $oIfx, '');
		$cuen_nom_cuen = consulta_string($sql_cuentas, 'cuen_nom_cuen', $oIfx, '');
		$cuen_ccos_cuen = consulta_string($sql_cuentas, 'cuen_ccos_cuen', $oIfx, '');
		$cuen_ccos_cuen = $infimp_cod_import;
		$cuen_cact_cuen = consulta_string($sql_cuentas, 'cuen_cact_cuen', $oIfx, '');

		/*
		$oReturn->assign("val_cta", "value", $infimp_val_infimp);
		$oReturn->assign("cod_cta", "value", $cuen_cod_cuen);
		$oReturn->assign("nom_cta", "value", $cuen_nom_cuen);
		$oReturn->assign("centro_costo_cuen", "value", $cuen_ccos_cuen);
		$oReturn->assign("centro_actividad", "value", $cuen_cact_cuen);
		$oReturn->assign("detalla_diario", "value", 'IMPORTACION_' . date('Y-m-d'));
		$oReturn->assign("val_cta", "value", $infimp_cod_import);
		//$oReturn->script("anadir_dasi()");

		*/


		// -----------------------------------------------------
		// Proceso extra borrar
		// -----------------------------------------------------

		$aDataDiar = $_SESSION['aDataGirdDiar'];

		$aLabelDiar = array(
			'Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
			'Debito Moneda Local', 		'Credito Moneda Local',
			'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar',
			'Centro Costo', 			'Centro Actividad'
		);

		$cred = 0;
		$deb  = number_format($infimp_val_infimp, 2, '.', '');

		// DIARIO
		$contd = count($aDataDiar);
		$aDataDiar[$contd][$aLabelDiar[0]] = floatval($contd);
		$aDataDiar[$contd][$aLabelDiar[1]] = $cuen_cod_cuen;
		$aDataDiar[$contd][$aLabelDiar[2]] = $cuen_nom_cuen;
		$aDataDiar[$contd][$aLabelDiar[3]] = '';
		$aDataDiar[$contd][$aLabelDiar[4]] = $coti;

		if ($mone_cod == $mone_base) {
			// moneda local
			$cre_tmp = 0;
			$deb_tmp = 0;
			if ($coti > 0) {
				$cre_tmp = round(($cred / $coti), 2);
			}

			if ($coti > 0) {
				$deb_tmp = round(($deb / $coti), 2);
			}

			$aDataDiar[$contd][$aLabelDiar[5]] = $deb;
			$aDataDiar[$contd][$aLabelDiar[6]] = $cred;
			$aDataDiar[$contd][$aLabelDiar[7]] = $deb_tmp;
			$aDataDiar[$contd][$aLabelDiar[8]] = $cre_tmp;
		} else {
			// moneda extra			
			$aDataDiar[$contd][$aLabelDiar[5]] = $deb * $coti;
			$aDataDiar[$contd][$aLabelDiar[6]] = $cred * $coti;
			$aDataDiar[$contd][$aLabelDiar[7]] = $deb;
			$aDataDiar[$contd][$aLabelDiar[8]] = $cred;
		}

		$vacio = '-1';

		$aDataDiar[$contd][$aLabelDiar[9]]  = 'IMPORTACION_' . date('Y-m-d');
		$aDataDiar[$contd][$aLabelDiar[10]] = '';
		$aDataDiar[$contd][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
													title = "Presione aqui para Eliminar"
													style="cursor: hand !important; cursor: pointer !important;"
													onclick="javascript:xajax_elimina_detalle_dir(' . $vacio . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
													alt="Eliminar"
													align="bottom" />';
		$aDataDiar[$contd][$aLabelDiar[12]] = $cuen_ccos_cuen;
		$aDataDiar[$contd][$aLabelDiar[13]] = 417;


		// DIARIO
		$sHtml = '';
		$_SESSION['aDataGirdDiar'] = $aDataDiar;
		$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
		$oReturn->assign("divDiario", "innerHTML", $sHtml);

		// -----------------------------------------------------
		// FIN Proceso extra borrar
		// -----------------------------------------------------



		// cuenta dos
		$sql_cuentas = "select cuen_cod_cuen, cuen_nom_cuen, cuen_nom_ingl, cuen_mov_cuen, cuen_bie_cuen, cuen_cact_cuen, cuen_ccos_cuen
				from saecuen where
				cuen_cod_empr = $idempresa and
				cuen_cod_cuen = '$parm_cod_cuen2'
				order by cuen_cod_cuen ";
		$cuen_cod_cuen = consulta_string($sql_cuentas, 'cuen_cod_cuen', $oIfx, '');
		$cuen_nom_cuen = consulta_string($sql_cuentas, 'cuen_nom_cuen', $oIfx, '');
		$cuen_ccos_cuen = consulta_string($sql_cuentas, 'cuen_ccos_cuen', $oIfx, '');
		$cuen_ccos_cuen = $infimp_cod_import;
		$cuen_cact_cuen = consulta_string($sql_cuentas, 'cuen_cact_cuen', $oIfx, '');

		$oReturn->assign("val_cta", "value", $infimp_val_infimp);
		$oReturn->assign("cod_cta", "value", $cuen_cod_cuen);
		$oReturn->assign("nom_cta", "value", $cuen_nom_cuen);
		$oReturn->assign("centro_costo_cuen", "value", $cuen_ccos_cuen);
		$oReturn->assign("centro_actividad", "value", $cuen_cact_cuen);
		$oReturn->assign("detalla_diario", "value", 'IMPORTACION_' . date('Y-m-d'));
		$oReturn->assign("val_cta", "value", $infimp_val_iva);
		$oReturn->assign("ccosn", "value", $infimp_cod_import);

		//$oReturn->script("anadir_dasi()");



		// -----------------------------------------------------
		// Proceso extra borrar
		// -----------------------------------------------------

		$aDataDiar = $_SESSION['aDataGirdDiar'];

		$aLabelDiar = array(
			'Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
			'Debito Moneda Local', 		'Credito Moneda Local',
			'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar',
			'Centro Costo', 			'Centro Actividad'
		);

		$cred = 0;
		$deb  = number_format($infimp_val_iva, 2, '.', '');

		// DIARIO
		$contd = count($aDataDiar);
		$aDataDiar[$contd][$aLabelDiar[0]] = floatval($contd);
		$aDataDiar[$contd][$aLabelDiar[1]] = $cuen_cod_cuen;
		$aDataDiar[$contd][$aLabelDiar[2]] = $cuen_nom_cuen;
		$aDataDiar[$contd][$aLabelDiar[3]] = '';
		$aDataDiar[$contd][$aLabelDiar[4]] = $coti;

		if ($mone_cod == $mone_base) {
			// moneda local
			$cre_tmp = 0;
			$deb_tmp = 0;
			if ($coti > 0) {
				$cre_tmp = round(($cred / $coti), 2);
			}

			if ($coti > 0) {
				$deb_tmp = round(($deb / $coti), 2);
			}

			$aDataDiar[$contd][$aLabelDiar[5]] = $deb;
			$aDataDiar[$contd][$aLabelDiar[6]] = $cred;
			$aDataDiar[$contd][$aLabelDiar[7]] = $deb_tmp;
			$aDataDiar[$contd][$aLabelDiar[8]] = $cre_tmp;
		} else {
			// moneda extra			
			$aDataDiar[$contd][$aLabelDiar[5]] = $deb * $coti;
			$aDataDiar[$contd][$aLabelDiar[6]] = $cred * $coti;
			$aDataDiar[$contd][$aLabelDiar[7]] = $deb;
			$aDataDiar[$contd][$aLabelDiar[8]] = $cred;
		}

		$vacio = '-1';

		$aDataDiar[$contd][$aLabelDiar[9]]  = 'IMPORTACION_' . date('Y-m-d');
		$aDataDiar[$contd][$aLabelDiar[10]] = '';
		$aDataDiar[$contd][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
													title = "Presione aqui para Eliminar"
													style="cursor: hand !important; cursor: pointer !important;"
													onclick="javascript:xajax_elimina_detalle_dir(' . $vacio . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
													alt="Eliminar"
													align="bottom" />';
		$aDataDiar[$contd][$aLabelDiar[12]] = $cuen_ccos_cuen;
		$aDataDiar[$contd][$aLabelDiar[13]] = 417;


		// DIARIO
		$sHtml = '';
		$_SESSION['aDataGirdDiar'] = $aDataDiar;
		$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
		$oReturn->assign("divDiario", "innerHTML", $sHtml);

		// -----------------------------------------------------
		// FIN Proceso extra borrar
		// -----------------------------------------------------





		// ----------------------------------------------------------------------------------
		// FIN Directorio
		// ----------------------------------------------------------------------------------


	}









	return $oReturn;
}

function mostrar_grid_dir($idempresa, $idsucursal)
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$aDataGrid  = $_SESSION['aDataGirdDir'];

	$aLabelGrid = array(
		'Id', 'Cliente', 'SubCliente', 'Tipo', 'Factura', 'Fec. Vence', 'Detalle',
		'Cotizacion', 'Debito Moneda Local', 'Credito Moneda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 'Modificar', 'Eliminar', 'DI'
	);

	$cont    = 0;
	$tot_cre = 0;
	$tot_deb = 0;
	foreach ($aDataGrid as $aValues) {
		$aux     = 0;
		foreach ($aValues as $aVal) {
			if ($aux == 0) {
				$aDatos[$cont][$aLabelGrid[$aux]] = ($cont + 1);
			} elseif ($aux == 1) {
				$sql = "select  clpv_nom_clpv from saeclpv where clpv_cod_clpv = $aVal and clpv_cod_empr = $idempresa";
				$clpv_nom = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
				$aDatos[$cont][$aLabelGrid[$aux]] = $clpv_nom;
			} elseif ($aux == 2) {
				if (!empty($aVal)) {
					$sql = "select ccli_nom_conta from saeccli where ccli_cod_ccli = $aVal and ccli_cod_empr = $idempresa";
					$ccli_nom = consulta_string_func($sql, 'ccli_nom_conta', $oIfx, '');
				} else {
					$ccli_nom = '';
				}
				$aDatos[$cont][$aLabelGrid[$aux]] = $ccli_nom;
			} elseif ($aux == 3) {
				$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
			} elseif ($aux == 4) {

				$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
			} elseif ($aux == 5) {
				$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
			} elseif ($aux == 6) {
				$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
			} elseif ($aux == 7) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 6), 6, '.', ',') . '</div>';
			} elseif ($aux == 8) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
				$tot_deb += $aVal;
			} elseif ($aux == 9) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
				$tot_cre += $aVal;
			} elseif ($aux == 10) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
			} elseif ($aux == 11) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
			} elseif ($aux == 12) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete.png"
															title = "Presione aqui para Eliminar"
															style="cursor:pointer; width:20px; height:20px; margin:auto; display:block;"
															onclick="javascript:xajax_elimina_detalle_dir(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ');"
															alt="Eliminar"
															align="bottom" />';
			} else
				$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
			$aux++;
		}
		$cont++;
	}
	$array = array('', '', '', '', '', '', '', '', $tot_deb, $tot_cre, '');

	return genera_grid($aDatos, $aLabelGrid, 'DIRECTORIO', 99, '', $array);
}

function elimina_detalle_dir($id = null, $idempresa, $idsucursal, $id_di = '', $id_ret = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	unset($aLabelGrid);
	$aLabelGrid = array(
		'Id', 'Cliente', 'SubCliente', 'Tipo', 'Factura', 'Fec. Vence', 'Detalle', 'Cotizacion', 'Debito Moneda Local', 'Credito Moneda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 'Modificar', 'Eliminar', 'DI'
	);

	unset($aDataGrid);
	$contador  = 0;

	$aDataGrid = $_SESSION['aDataGirdDir'];
	$contador  = count($aDataGrid);
	//$oReturn->alert('DIR '.$contador);
	if ($contador > 1) {
		unset($aDataGrid[$id]);
		$aDataGrid = array_values($aDataGrid);
		$cont 	   = 0;

		foreach ($aDataGrid as $aValues) {
			$aux     = 0;
			foreach ($aValues as $aVal) {
				if ($aux == 0) {
					$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . ($cont + 1) . '</div>';
				} elseif ($aux == 12) {
					$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="center">
																			<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil.png"
																			title = "Presione aqui para Eliminar"
																			style="cursor: hand !important; cursor: pointer !important;"
																			onclick="javascript:xajax_elimina_detalle_dir(' . $cont . ');"
																			alt="Eliminar"
																			align="bottom" />
																		</div>';
				} else
					$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
				$aux++;
			}
			$cont++;
		}

		$_SESSION['aDataGirdDir'] = $aDatos;
		$sHtml = mostrar_grid_dir($idempresa, $idsucursal);
		$oReturn->assign("divDir", "innerHTML", $sHtml);
	} else {
		unset($aDataGrid[0]);
		$_SESSION['aDataGirdDir'] = $aDatos;
		$sHtml = "";
		$oReturn->assign("divDir", "innerHTML", $sHtml);
	}



	// DIARIO
	$aLabelDiar = array(
		'Fila', 			 'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 	'Detalle',		'Modificar',
		'Eliminar',				'Centro Costo',   			'Centro Actividad',
		'DIR', 	             'RET'
	);
	unset($aDataGrid);
	$contador   = 0;
	$aDataGrid  = $_SESSION['aDataGirdDiar'];
	$contador   = count($aDataGrid);
	//$oReturn->alert('DI '.$contador);
	unset($aDatos);
	if ($contador > 1) {
		unset($aDataGrid[$id_di]);
		$aDataGrid = array_values($aDataGrid);
		$cont = 0;
		foreach ($aDataGrid as $aValues) {
			$aux = 0;
			foreach ($aValues as $aVal) {
				if ($aux == 0) {
					$aDatos[$cont][$aLabelDiar[$aux]] = '<div align="right">' . ($cont + 1) . '</div>';
				} elseif ($aux == 10) {
					$aDatos[$cont][$aLabelDiar[$aux]] = '<div align="center">
                                                                <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil.png"
                                                                title = "Presione aqui para Eliminar"
                                                                style="cursor: hand !important; cursor: pointer !important;"
                                                                onclick="javascript:modificar_valor(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ');"
                                                                alt="Eliminar"
                                                                align="bottom" />
                                                            </div>';
				} else
					$aDatos[$cont][$aLabelDiar[$aux]] = $aVal;
				$aux++;
			}
			$cont++;
		}

		$_SESSION['aDataGirdDiar'] = $aDatos;
		$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
		$oReturn->assign("divDiario", "innerHTML", $sHtml);
	} else {
		unset($aDataGrid[0]);
		$_SESSION['aDataGirdDiar'] = $aDatos;
		$sHtml = "";
		$oReturn->assign("divDiario", "innerHTML", $sHtml);
	}


	// RETENCION
	if ($id_ret >= 0) {
		unset($aLabelGrid);
		$aLabelGrid = array(
			'Fila', 			'Cta Ret', 				'Cliente', 			'Factura', 			'Ret Cliente', 				'Porc(%)', 				'Base Impo',
			'Valor', 			'N.- Retencion', 		'Detalle', 	 		'Origen', 			'Cotizacion',  				'Debito Moneda Local', 'Credito Monda Local',
			'Debito Moneda Ext', 'Credito Moneda Ext', 	'Modificar', 		'Eliminar',			'DI'
		);
		unset($aDataGrid);
		$contador   = 0;

		$aDataGrid  = $_SESSION['aDataGirdRet'];
		$contador   = count($aDataGrid);
		//$oReturn->alert('ret '.$contador);
		unset($aDatos);
		if ($contador > 1) {
			unset($aDataGrid[$id_ret]);
			$aDataGrid = array_values($aDataGrid);
			$cont = 0;
			foreach ($aDataGrid as $aValues) {
				$aux = 0;
				foreach ($aValues as $aVal) {
					if ($aux == 0) {
						$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . ($cont + 1) . '</div>';
					} elseif ($aux == 16) {
						$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="center">
																	<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil.png"
																	title = "Presione aqui para Eliminar"
																	style="cursor: hand !important; cursor: pointer !important;"
																	onclick="javascript:xajax_elimina_detalle_dir(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ');"
																	alt="Eliminar"
																	align="bottom" />
																</div>';
					} else
						$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
					$aux++;
				}
				$cont++;
			}

			$_SESSION['aDataGirdRet'] = $aDatos;
			$sHtml = mostrar_grid_ret($idempresa, $idsucursal);
			$oReturn->assign("divRet", "innerHTML", $sHtml);
		} else {
			unset($aDataGrid[0]);
			$_SESSION['aDataGirdRet'] = $aDatos;
			$sHtml = "";
			$oReturn->assign("divRet", "innerHTML", $sHtml);
		}
	} // fin reten if


	// TOTAL DIARIO
	$oReturn->script("total_diario();");
	return $oReturn;
}







// DIARIO
function agrega_modifica_grid_dia($nTipo = 0, $aForm = '', $id = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$aDataDiar = $_SESSION['aDataGirdDiar'];

	$aLabelDiar = array(
		'Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar',
		'Centro Costo', 			'Centro Actividad'
	);

	$oReturn = new xajaxResponse();

	// VARIABLES
	$cod_cta    = $aForm["cod_cta"];
	$nom_cta    = $aForm["nom_cta"];
	$val_cta    = str_replace(",", "", $aForm['val_cta']);
	$idsucursal = $aForm["sucursal"];
	$tipo       = $aForm['crdb'];
	$documento  = $aForm['documento'];
	$idempresa  = $aForm["empresa"];
	$mone_cod   = $aForm["moneda"];
	$detalle    = $aForm["detalla_diario"];
	$ccosn_cod  = $aForm["ccosn"];
	$act_cod    = $aForm["actividad"];

	if (!empty($cod_cta)) {

		$sql = "select COALESCE(cuen_cact_cuen,'N') AS cuen_cact_cuen, COALESCE(cuen_ccos_cuen, 'N')  as cuen_ccos_cuen
				from saecuen where
				cuen_cod_empr = $idempresa and
				cuen_cod_cuen = '$cod_cta'	";
		if ($oIfx->Query($sql)) {
			if ($oIfx->NumFilas() > 0) {
				$ccos_sn   = $oIfx->f('cuen_ccos_cuen');
				$cact_sn   = $oIfx->f('cuen_cact_cuen');
			} else {
				$ccos_sn   = 'N';
				$cact_sn   = 'N';
			}
		}

		$ctrl_sn  = 0;
		$msn_erro = '';
		if ($ccos_sn == 'N' && $cact_sn == 'N') {
			$ctrl_sn = 1;
		}

		if ($ccos_sn == 'N' && $cact_sn == 'S') {
			if (strlen($act_cod) > 0) {
				$ctrl_sn = 1;
			} else {
				$msn_erro = '!!! Por favor Seleccione Centro Actividad...';
				$ctrl_sn = 0;
			}
		}

		if ($ccos_sn == 'S' && $cact_sn == 'N') {
			if (strlen($ccosn_cod) > 0) {
				$ctrl_sn = 1;
			} else {
				$msn_erro = '!!! Por favor Seleccione Centro Costo...';
				$ctrl_sn = 0;
			}
		}

		if ($ccos_sn == 'S' && $cact_sn == 'S') {
			if (strlen($ccosn_cod) > 0 && strlen($act_cod) > 0) {
				$ctrl_sn = 1;
			} else {
				$msn_erro = '!!! Por favor Seleccione Centro Costo - Centro Actividad. ...';
				$ctrl_sn = 0;
			}
		}



		$sql        = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
		$mone_base  = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

		if ($mone_cod == $mone_base) {
			$sql      = "select pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa ";
			$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

			$sql = "select tcam_val_tcam from saetcam where
				mone_cod_empr = $idempresa and
				tcam_cod_mone = $mone_extr and
				tcam_fec_tcam in (
									select max(tcam_fec_tcam)  from saetcam where
											mone_cod_empr = $idempresa and
											tcam_cod_mone = $mone_extr
								)  ";

			$coti = $coti = $aForm["cotizacion_ext"]; //consulta_string($sql, 'tcam_val_tcam', $oIfx, 0);
		} else {
			$coti = $aForm["cotizacion"];
		}


		if ($tipo == 'CR') {
			$cred = $val_cta;
			$deb  = 0;
		} elseif ($tipo == 'DB') {
			$cred = 0;
			$deb  = $val_cta;
		}


		if ($ctrl_sn == 1) {
			if ($nTipo == 0) {
				// DIARIO
				$contd = count($aDataDiar);
				$aDataDiar[$contd][$aLabelDiar[0]] = floatval($contd);
				$aDataDiar[$contd][$aLabelDiar[1]] = $cod_cta;
				$aDataDiar[$contd][$aLabelDiar[2]] = $nom_cta;
				$aDataDiar[$contd][$aLabelDiar[3]] = $documento;
				$aDataDiar[$contd][$aLabelDiar[4]] = $coti;

				if ($mone_cod == $mone_base) {
					// moneda local
					$cre_tmp = 0;
					$deb_tmp = 0;
					if ($coti > 0) {
						$cre_tmp = round(($cred / $coti), 2);
					}

					if ($coti > 0) {
						$deb_tmp = round(($deb / $coti), 2);
					}

					$aDataDiar[$contd][$aLabelDiar[5]] = $deb;
					$aDataDiar[$contd][$aLabelDiar[6]] = $cred;
					$aDataDiar[$contd][$aLabelDiar[7]] = $deb_tmp;
					$aDataDiar[$contd][$aLabelDiar[8]] = $cre_tmp;
				} else {
					// moneda extra			
					$aDataDiar[$contd][$aLabelDiar[5]] = $deb * $coti;
					$aDataDiar[$contd][$aLabelDiar[6]] = $cred * $coti;
					$aDataDiar[$contd][$aLabelDiar[7]] = $deb;
					$aDataDiar[$contd][$aLabelDiar[8]] = $cred;
				}

				$vacio = '-1';

				$aDataDiar[$contd][$aLabelDiar[9]]  = $detalle;
				$aDataDiar[$contd][$aLabelDiar[10]] = '';
				$aDataDiar[$contd][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
															title = "Presione aqui para Eliminar"
															style="cursor: hand !important; cursor: pointer !important;"
															onclick="javascript:xajax_elimina_detalle_dir(' . $vacio . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
															alt="Eliminar"
															align="bottom" />';
				$aDataDiar[$contd][$aLabelDiar[12]] = $ccosn_cod;
				$aDataDiar[$contd][$aLabelDiar[13]] = $act_cod;
			}

			// DIARIO
			$sHtml = '';
			$_SESSION['aDataGirdDiar'] = $aDataDiar;
			$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
			$oReturn->assign("divDiario", "innerHTML", $sHtml);

			$oReturn->assign("cod_cta", "value", '');
			$oReturn->assign("nom_cta", "value", '');
			$oReturn->assign("val_cta", "value", '');
			$oReturn->assign("documento", "value", '');

			$oReturn->script("total_diario();");


			// --------------------------------------------------------------------------
			// CUENTAS ADICIONALES PERU
			// --------------------------------------------------------------------------
			$empr_cod_pais = $_SESSION['U_PAIS_COD'];
			$sql_codInter_pais = "SELECT pais_codigo_inter from saepais where pais_cod_pais = $empr_cod_pais;";
			$codigo_pais = consulta_string_func($sql_codInter_pais, 'pais_codigo_inter', $oIfx, 0);

			$sql_pcon = "SELECT pcon_cue_niif from saepcon WHERE pcon_cod_empr = $idempresa;";
			$pcon_cue_niif = consulta_string_func($sql_pcon, 'pcon_cue_niif', $oIfx, 0);

			if ($codigo_pais == 51 && $pcon_cue_niif == 'S') {
				// Cuando es Peru baja dos cuentas adicionales: cuan_ana_deb y cuen_anal_cre
				$sql_cuen = "SELECT cuen_ana_deb, cuen_ana_cre from saecuen where cuen_cod_cuen = '$cod_cta';";
				$cuen_ana_deb = consulta_string_func($sql_cuen, 'cuen_ana_deb', $oIfx, 0);
				$cuen_ana_cre = consulta_string_func($sql_cuen, 'cuen_ana_cre', $oIfx, 0);
				$existe_cuenta = 'N';
				foreach ($aDataDiar as $key => $value) {
					if ($value['Cuenta'] == $cuen_ana_deb || $value['Cuenta'] == $cuen_ana_cre) {
						$existe_cuenta = 'S';
					}
				}
				if ($existe_cuenta == 'N') {

					$total_adicional = $aForm['val_cta'];

					// --------------------------------------
					// DEBITO
					// --------------------------------------

					$sql_cuen_deb = "SELECT cuen_nom_cuen from saecuen where cuen_cod_cuen = '$cuen_ana_deb';";
					$cuen_nom_cuen_deb = consulta_string_func($sql_cuen_deb, 'cuen_nom_cuen', $oIfx, 0);
					$oReturn->assign("cod_cta", "value", $cuen_ana_deb);
					$oReturn->assign("nom_cta", "value", $cuen_nom_cuen_deb);
					$oReturn->assign("detalla_diario", "value", 'CUENTA ADICIONAL DEBITO');
					// CR => Credito || DB => Debito
					$oReturn->assign("crdb", "value", 'DB');
					$oReturn->assign("val_cta", "value", $total_adicional);
					$oReturn->script("anadir_dasi();");

					// --------------------------------------
					// CREDITO
					// --------------------------------------


					$sql_cuen_cre = "SELECT cuen_nom_cuen from saecuen where cuen_cod_cuen = '$cuen_ana_cre';";
					$cuen_nom_cuen_cre = consulta_string_func($sql_cuen_cre, 'cuen_nom_cuen', $oIfx, 0);
					$oReturn->assign("cod_cta", "value", $cuen_ana_cre);
					$oReturn->assign("nom_cta", "value", $cuen_nom_cuen_cre);
					$oReturn->assign("detalla_diario", "value", 'CUENTA ADICIONAL CREDITO');
					// CR => Credito || DB => Debito
					$oReturn->assign("crdb", "value", 'CR');
					$oReturn->assign("val_cta", "value", $total_adicional);
					$oReturn->script("anadir_dasi();");
				}
			}

			// --------------------------------------------------------------------------
			// FIN CUENTAS ADICIONALES PERU
			// --------------------------------------------------------------------------



		} else {
			$oReturn->alert($msn_erro);
		}
	}

	return $oReturn;
}

// DIARIO ASIENTO INICIAL

function agrega_modifica_grid_dia_asi_inic($nTipo = 0, $aForm = '', $arrayBal = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oIfxA = new Dbo;
	$oIfxA->DSN = $DSN_Ifx;
	$oIfxA->Conectar();

	$aDataDiar = $_SESSION['aDataGirdDiar'];

	// Decodificar el JSON a un array
	$array = json_decode($arrayBal, true);

	$id_empresa = $array[0];
	$id_ejer    = $array[1];
	$moneda     = $array[2];
	$nivel    	= $array[3];
	$id_mes     = $array[4];
	$detalle    = $array[5];
	

	$documento='';
	$idsucursal = $aForm["sucursal"];

	

	$aLabelDiar = array(
		'Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar',
		'Centro Costo', 			'Centro Actividad'
	);

	$oReturn = new xajaxResponse();


	$sql = "select pcon_mon_base from saepcon where pcon_cod_empr = $id_empresa ";
		$mone_base  = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

		if ($moneda == $mone_base) {
			$sql      = "select pcon_seg_mone from saepcon where pcon_cod_empr = $id_empresa ";
			$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

			$sql = "select tcam_val_tcam from saetcam where
				mone_cod_empr = $id_empresa and
				tcam_cod_mone = $mone_extr and
				tcam_fec_tcam in (
									select max(tcam_fec_tcam)  from saetcam where
											mone_cod_empr = $id_empresa and
											tcam_cod_mone = $mone_extr
								)  ";

			$coti = $coti = $aForm["cotizacion_ext"]; //consulta_string($sql, 'tcam_val_tcam', $oIfx, 0);
		} else {
			$coti = $aForm["cotizacion"];
		}



		//--------------------------------
			// SALDO A NIVEL 1
			//--------------------------------
			$sql2 = "SELECT * from  sp_r_bal_gen_web( $id_empresa , $id_ejer, $id_mes, '$nivel', 1, $moneda);";
			$oIfx->Query($sql2);



			$sql2 = "SELECT
					cuen_cod_cuen as cuen_cod,
					cuen_nom_cuen as cuen_nom,
					cuen_mov_cuen as cuen_mov,
					cuen_niv_cuen as cuen_niv,
					saldo,
					grupo 
					FROM
						t_saldos1 
					ORDER BY
						6,
						1";


						$total_dife=0;
						if ($oIfx->Query($sql2)) {
							//            if($oIfx->NumFilas()>0){
							do {
			
								$cuen_cod = $oIfx->f('cuen_cod');
								$cuen_nom = $oIfx->f('cuen_nom');
								$cuen_mov = $oIfx->f('cuen_mov');
								$cuen_niv = $oIfx->f('cuen_niv');
			
								$saldo    = $oIfx->f('saldo');
								$grupo    = $oIfx->f('grupo');

								if ($cuen_niv == 1) {
									$total_dife += $saldo;

									
								}

							} while ($oIfx->SiguienteRegistro());
						}
						//echo $total_dife;exit;
			//---------------------------


			$sql_verifica_peru = "SELECT pcon_cue_niif from saepcon";
			$verifica_peru = consulta_string($sql_verifica_peru, 'pcon_cue_niif', $oIfx, 'N');



			if ($verifica_peru == 'S') {
				$sql = "select * from  sp_r_bal_gen_web_peru( $id_empresa , $id_ejer, $id_mes, '$nivel', 1, $moneda);";
				$oIfx->Query($sql);
			} else {
				$sql = "select * from  sp_asiento_inic_web( $id_empresa , $id_ejer, $id_mes, '$nivel', 1, $moneda);";
				$oIfx->Query($sql);
			}


			$sql = "SELECT
					cuen_cod_cuen as cuen_cod,
					cuen_nom_cuen as cuen_nom,
					cuen_mov_cuen as cuen_mov,
					cuen_niv_cuen as cuen_niv,
					saldo,
					grupo 
					FROM
						t_saldos1 
					ORDER BY
						6,
						1";

			//echo $sql;exit;


			$x     = 0;
			$total = 0;

			$total_credito = 0;
			$ttoal_debito = 0;
			
			if ($oIfx->Query($sql)) {
				if ($oIfx->NumFilas() > 0) {
					do {

						$cuen_cod = $oIfx->f('cuen_cod');
						$cuen_nom = $oIfx->f('cuen_nom');
						$cuen_mov = $oIfx->f('cuen_mov');
						$cuen_niv = $oIfx->f('cuen_niv');

						$saldo    = $oIfx->f('saldo');
						$grupo    = $oIfx->f('grupo');

						if ($saldo != 0) {

							$c_act      = $array[6];
							$ccos       = $array[7];
						///VALIDACION CENTRO DE ACTIVIDAD Y CENTRO DE COSTOS
							$sql = "select COALESCE(cuen_cact_cuen,'N') AS cuen_cact_cuen, COALESCE(cuen_ccos_cuen, 'N')  as cuen_ccos_cuen
							from saecuen where
							cuen_cod_empr = $id_empresa and
							cuen_cod_cuen = '$cuen_cod'	";

							
							if ($oIfxA->Query($sql)) {
								if ($oIfxA->NumFilas() > 0) {
									$ccos_sn   = $oIfxA->f('cuen_ccos_cuen');
									$cact_sn   = $oIfxA->f('cuen_cact_cuen');
								} else {
									$ccos_sn   = 'N';
									$cact_sn   = 'N';
								}
							}

							

							if($ccos_sn=='N'){
								$ccos='';
							}
							if($cact_sn=='N'){
								$c_act='';
							}

		

							$empieza = substr($cuen_cod, 0, 2);
							$p_o_n = substr($saldo, 0, 1);
							
							if ($empieza == '1.') {


								if ($p_o_n == '-') {

									$credito = $saldo;
									$debito = 0;
								} else {
									$debito = $saldo;
									$credito = 0;
								}
							} elseif ($empieza == '2.') {

								if ($p_o_n != '-') {

									$debito = $saldo;
									$credito = 0;
								} else {
									$credito = $saldo;
									$debito = 0;
								}
							} elseif ($empieza == '3.') {

								if ($p_o_n != '-') {

									$debito = $saldo;
									$credito = 0;
								} else {
									$credito = $saldo;
									$debito = 0;
								}
							}


							$credito = str_replace('-', '', $credito);
							$total_dife = str_replace('-', '', $total_dife);

							$total_credito += $credito;
							$ttoal_debito += $debito;

							if($total_credito>$ttoal_debito){
								
								$debito_dife=$total_dife;
								$general_debito=$ttoal_debito+$total_dife;

							}else{

								$debito_dife=0;
								$general_debito=$ttoal_debito;

							}

							if($ttoal_debito>$total_credito){
								
								$credito_dife=$total_dife;
								$general_credito=$total_credito+$total_dife;

							}else{

								$credito_dife=0;
								$general_credito=$total_credito;

							}


							// DIARIO
						$aDataDiar[$x][$aLabelDiar[0]] = $x;
						$aDataDiar[$x][$aLabelDiar[1]] = $cuen_cod;
						$aDataDiar[$x][$aLabelDiar[2]] = $cuen_nom;
						$aDataDiar[$x][$aLabelDiar[3]] = $documento;
						$aDataDiar[$x][$aLabelDiar[4]] = $coti;


						if ($moneda == $mone_base) {
							// moneda local
							$cre_tmp = 0;
							$deb_tmp = 0;
							if ($coti > 0) {
								$cre_tmp = round(($credito / $coti), 2);
							}
		
							if ($coti > 0) {
								$deb_tmp = round(($debito / $coti), 2);
							}
		
							$aDataDiar[$x][$aLabelDiar[5]] = $debito;
							$aDataDiar[$x][$aLabelDiar[6]] = $credito;
							$aDataDiar[$x][$aLabelDiar[7]] = $deb_tmp;
							$aDataDiar[$x][$aLabelDiar[8]] = $cre_tmp;
						} else {
							// moneda extra			
							$aDataDiar[$x][$aLabelDiar[5]] = $debito * $coti;
							$aDataDiar[$x][$aLabelDiar[6]] = $credito * $coti;
							$aDataDiar[$x][$aLabelDiar[7]] = $debito;
							$aDataDiar[$x][$aLabelDiar[8]] = $credito;
						}
		
						$vacio = '-1';
		
						$aDataDiar[$x][$aLabelDiar[9]]  = $detalle;
						$aDataDiar[$x][$aLabelDiar[10]] = '';
						$aDataDiar[$x][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																	title = "Presione aqui para Eliminar"
																	style="cursor: hand !important; cursor: pointer !important;"
																	onclick="javascript:xajax_elimina_detalle_dir(' . $vacio . ', ' . $id_empresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
																	alt="Eliminar"
																	align="bottom" />';
						$aDataDiar[$x][$aLabelDiar[12]] = $ccos;
						$aDataDiar[$x][$aLabelDiar[13]] = $c_act;
							
							$x++;
						}

					} while ($oIfx->SiguienteRegistro());

					$c_act      = $array[6];
					$ccos       = $array[7];
					//CUENTA DE UTILIDAD

					$sql="SELECT pcon_cta_resu from saepcon";
					$cuen_dife = consulta_string($sql, 'pcon_cta_resu', $oIfxA, 'N');

					$sql = "select cuen_nom_cuen
							from saecuen where
							cuen_cod_empr = $id_empresa and
							cuen_cod_cuen = '$cuen_dife'	";

					$cuen_nom = consulta_string($sql, 'cuen_nom_cuen', $oIfxA, '');



					///VALIDACION CENTRO DE ACTIVIDAD Y CENTRO DE COSTOS
					$sql = "select COALESCE(cuen_cact_cuen,'N') AS cuen_cact_cuen, COALESCE(cuen_ccos_cuen, 'N')  as cuen_ccos_cuen
					from saecuen where
					cuen_cod_empr = $id_empresa and
					cuen_cod_cuen = '$cuen_dife'	";
					if ($oIfxA->Query($sql)) {
						if ($oIfxA->NumFilas() > 0) {
							$ccos_sn   = $oIfxA->f('cuen_ccos_cuen');
							$cact_sn   = $oIfxA->f('cuen_cact_cuen');
						} else {
							$ccos_sn   = 'N';
							$cact_sn   = 'N';
						}
					}

					if($ccos_sn=='N') $ccos='';
					if($cact_sn=='N') $c_act='';

					// DIARIO
				$aDataDiar[$x][$aLabelDiar[0]] = $x;
				$aDataDiar[$x][$aLabelDiar[1]] = $cuen_dife;
				$aDataDiar[$x][$aLabelDiar[2]] = $cuen_nom;
				$aDataDiar[$x][$aLabelDiar[3]] = $documento;
				$aDataDiar[$x][$aLabelDiar[4]] = $coti;


				if ($moneda == $mone_base) {
					// moneda local
					$cre_tmp = 0;
					$deb_tmp = 0;
					if ($coti > 0) {
						$cre_tmp = round(($credito_dife / $coti), 2);
					}

					if ($coti > 0) {
						$deb_tmp = round(($debito_dife / $coti), 2);
					}

					$aDataDiar[$x][$aLabelDiar[5]] = $debito_dife;
					$aDataDiar[$x][$aLabelDiar[6]] = $credito_dife;
					$aDataDiar[$x][$aLabelDiar[7]] = $deb_tmp;
					$aDataDiar[$x][$aLabelDiar[8]] = $cre_tmp;
				} else {
					// moneda extra			
					$aDataDiar[$x][$aLabelDiar[5]] = $debito_dife * $coti;
					$aDataDiar[$x][$aLabelDiar[6]] = $credito_dife * $coti;
					$aDataDiar[$x][$aLabelDiar[7]] = $debito_dife;
					$aDataDiar[$x][$aLabelDiar[8]] = $credito_dife;
				}

				$vacio = '-1';

				$aDataDiar[$x][$aLabelDiar[9]]  = $detalle;
				$aDataDiar[$x][$aLabelDiar[10]] = '';
				$aDataDiar[$x][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
															title = "Presione aqui para Eliminar"
															style="cursor: hand !important; cursor: pointer !important;"
															onclick="javascript:xajax_elimina_detalle_dir(' . $vacio . ', ' . $id_empresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
															alt="Eliminar"
															align="bottom" />';
				$aDataDiar[$x][$aLabelDiar[12]] = $ccos;
				$aDataDiar[$x][$aLabelDiar[13]] = $c_act;
	
				}
			}
			$oIfx->Free();



			// DIARIO
			$sHtml = '';
			$_SESSION['aDataGirdDiar'] = $aDataDiar;
			$sHtml = mostrar_grid_dia($id_empresa, $idsucursal);
			$oReturn->assign("divDiario", "innerHTML", $sHtml);

			$oReturn->assign("cod_cta", "value", '');
			$oReturn->assign("nom_cta", "value", '');
			$oReturn->assign("val_cta", "value", '');
			$oReturn->assign("documento", "value", '');
			$oReturn->assign("detalle", "value", $detalle);
			$oReturn->assign("cliente_nombre", "value", 'SALDO INICIAL');

			$fecha=date('Y-01-01');
			$oReturn->assign("fecha", "value", $fecha);
			$oReturn->assign("tipo_doc", "value", '003');


			$oReturn->script("total_diario();");


	return $oReturn;
}

function mostrar_grid_dia($idempresa, $idsucursal)
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN, $DSN_Ifx;

	$oCnx = new Dbo();
	$oCnx->DSN = $DSN;
	$oCnx->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$idempresa  = $_SESSION['U_EMPRESA'];
	$aDataGrid  = $_SESSION['aDataGirdDiar'];

	$aLabelGrid = array(
		'Fila', 			 'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 	'Detalle',		'Modificar',
		'Eliminar',				'Centro Costo',   			'Centro Actividad',
		'DIR',				 'RET'
	);

	$cont    = 0;
	$tot_cre = 0;
	$tot_cre_ext = 0;
	$tot_deb = 0;
	$tot_deb_ext = 0;
	foreach ($aDataGrid as $aValues) {
		$aux = 0;
		foreach ($aValues as $aVal) {
			if ($aux == 0) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . ($cont + 1) . '</div>';
			} elseif ($aux == 1) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="left">' . $aVal . '</div>';
			} elseif ($aux == 2) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="left">' . $aVal . '</div>';
			} elseif ($aux == 4) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 6), 6, '.', ',') . '</div>';
			} elseif ($aux == 5) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
				$tot_deb += $aVal;
			} elseif ($aux == 6) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
				$tot_cre += $aVal;
			} elseif ($aux == 7) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
				$tot_deb_ext += $aVal;
			} elseif ($aux == 8) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
				$tot_cre_ext += $aVal;
			} elseif ($aux == 10) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="center">
                                                                <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil.png"
                                                                title = "Presione aqui para Modificar"
                                                                style="cursor: hand !important; cursor: pointer !important;"
                                                                onclick="javascript:modificar_valor(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ');"
                                                                alt="Modificar"
                                                                align="bottom" />
                                                            </div>';
			} elseif ($aux == 12) {
				// CENTRO COSTO 
				$sql = "select ccosn_cod_ccosn, ( ccosn_nom_ccosn || ' - ' || ccosn_cod_ccosn ) as  ccosn_nom_ccosn from saeccosn where
									ccosn_cod_empr  = $idempresa and
									ccosn_mov_ccosn = 1 and
									ccosn_cod_ccosn = '$aVal' ";
				$ccosn_nom = consulta_string_func($sql, 'ccosn_nom_ccosn', $oIfx, '');
				$aDatos[$cont][$aLabelGrid[$aux]] = $ccosn_nom;
			} elseif ($aux == 13) {
				// CENTRO ACTIVIDAD
				$sql = "select cact_cod_cact , ( cact_nom_cact ||  ' - ' || cact_cod_cact ) as cact_nom_cact from saecact where
										cact_cod_empr = $idempresa and
										cact_cod_cact = '$aVal'  ";
				$act_nom = consulta_string_func($sql, 'cact_nom_cact', $oIfx, '');
				$aDatos[$cont][$aLabelGrid[$aux]] = $act_nom;
			} else
				$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
			$aux++;
		}
		$cont++;
	}
	$array = array('', '', '', '', '', $tot_deb, $tot_cre, $tot_deb_ext, $tot_cre_ext);
	return genera_grid($aDatos, $aLabelGrid, 'DIARIO', 99, '', $array);
}

function elimina_detalle_dia($id = null, $idempresa, $idsucursal)
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oReturn = new xajaxResponse();

	$aLabelDiar = array(
		'Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion', 		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar', 		'Centro Costo', 			'Centro Actividad'
	);

	$aDataGrid = $_SESSION['aDataGirdDiar'];
	$contador = count($aDataGrid);
	if ($contador > 1) {
		unset($aDataGrid[$id]);
		$_SESSION['aDataGirdDiar'] = $aDataGrid;
		$sHtml = mostrar_grid_dir($idempresa, $idsucursal);
		$oReturn->assign("divDiario", "innerHTML", $sHtml);
	} else {
		unset($aDataGrid[0]);
		$_SESSION['aDataGirdDiar'] = $aDatos;
		$sHtml = "";
		$oReturn->assign("divDiario", "innerHTML", $sHtml);
	}


	return $oReturn;
}



// RETENCION
function agrega_modifica_grid_ret($nTipo = 0, $aForm = '', $id = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$aDataGrid = $_SESSION['aDataGirdRet'];
	$aDataDiar = $_SESSION['aDataGirdDiar'];

	$aLabelGrid = array(
		'Fila', 			'Cta Ret', 				'Cliente', 			'Factura', 			'Ret Cliente', 				'Porc(%)', 				'Base Impo',
		'Valor', 			'N.- Retencion', 		'Detalle', 	 		'Origen', 			'Cotizacion',  				'Debito Moneda Local', 'Credito Monda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 	'Modificar', 		'Eliminar',			'DI'
	);

	$aLabelDiar = array(
		'Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar',
		'Centro Costo', 			'Centro Actividad',  'DIR',				 'RET'
	);

	$oReturn = new xajaxResponse();

	// VARIABLES
	$idempresa = $aForm["empresa"];
	$idsucursal = $aForm["sucursal"];
	$cod_ret   = $aForm["cod_ret"];
	$fact_ret  = $aForm["fact_ret"];
	$clpv_ret  = $aForm["ret_clpv"];
	$porc_ret  = $aForm["ret_porc"];
	$base_ret  = str_replace(",", "", $aForm['ret_base']);
	$val_ret   = $aForm["valor_retenido"];

	if (empty($val_ret)) {
		$val_ret = round(($base_ret * $porc_ret / 100), 2);
	}

	$num_ret   = $aForm["ret_num"];
	$det_ret   = $aForm["ret_det"];
	$cta_deb   = $aForm["cta_deb"];
	$cta_cre   = $aForm["cta_cre"];
	$tipo      = 'DB';
	$clpv_nom  = $aForm["clpv_nom"];
	$clpv_cod  = $aForm["clpv_cod"];
	$doc  = $aForm["documento"];
	$origen  = $aForm["origen"];

	$mone_cod   = $aForm["moneda"];
	$sql        = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
	$mone_base  = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

	if ($mone_cod == $mone_base) {
		$sql      = "select pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa ";
		$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

		$sql = "select tcam_val_tcam from saetcam where
                    mone_cod_empr = $idempresa and
                    tcam_cod_mone = $mone_extr and
                    tcam_fec_tcam in (
                                        select max(tcam_fec_tcam)  from saetcam where
                                                mone_cod_empr = $idempresa and
                                                tcam_cod_mone = $mone_extr
                                    )  ";

		$coti = $aForm["cotizacion_ext"];
	} else {
		$coti = $aForm["cotizacion"];
	}

	$val_deb   = 0;
	$val_cre   = 0;
	if ($tipo == 'CR') {
		// CREDITO
		$sql = "select cuen_nom_cuen from saecuen where 
                    cuen_cod_empr = $idempresa and
                    cuen_cod_cuen = '$cta_cre' ";
		$val_cre = $val_ret;
	} else {
		// DEBITO
		$sql = "select cuen_nom_cuen from saecuen where 
                    cuen_cod_empr = $idempresa and
                    cuen_cod_cuen = '$cta_deb' ";
		$val_deb = $val_ret;
	}
	$cuen_nom = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

	if ($nTipo == 0) {
		// RETENCION
		$cont = count($aDataGrid);
		$aDataGrid[$cont][$aLabelGrid[0]] = floatval($cont);
		$aDataGrid[$cont][$aLabelGrid[1]] = $cod_ret;
		$aDataGrid[$cont][$aLabelGrid[2]] = $clpv_cod;
		$aDataGrid[$cont][$aLabelGrid[3]] = $fact_ret;
		$aDataGrid[$cont][$aLabelGrid[4]] = $clpv_ret;
		$aDataGrid[$cont][$aLabelGrid[5]] = $porc_ret;
		$aDataGrid[$cont][$aLabelGrid[6]] = $base_ret;
		$aDataGrid[$cont][$aLabelGrid[7]] = $val_ret;
		$aDataGrid[$cont][$aLabelGrid[8]] = $num_ret;
		$aDataGrid[$cont][$aLabelGrid[9]] = $det_ret;
		$aDataGrid[$cont][$aLabelGrid[10]] = $origen;
		$aDataGrid[$cont][$aLabelGrid[11]] = $coti;

		if ($mone_cod == $mone_base) {
			// moneda local
			$cre_tmp = 0;
			$deb_tmp = 0;
			if ($coti > 0) {
				$cre_tmp = round(($val_cre / $coti), 2);
			}

			if ($coti > 0) {
				$deb_tmp = round(($val_deb / $coti), 2);
			}

			$aDataGrid[$cont][$aLabelGrid[12]] = 0;
			$aDataGrid[$cont][$aLabelGrid[13]] = $val_cre;
			$aDataGrid[$cont][$aLabelGrid[14]] = $val_ret;
			$aDataGrid[$cont][$aLabelGrid[15]] = 0;
		} else {
			// moneda extra

			$aDataGrid[$cont][$aLabelGrid[12]] = $val_deb * $coti;
			$aDataGrid[$cont][$aLabelGrid[13]] = $val_cre * $coti;

			$aDataGrid[$cont][$aLabelGrid[14]] = $val_deb;
			$aDataGrid[$cont][$aLabelGrid[15]] = 0;
		}

		$contd = count($aDataDiar);
		$aDataGrid[$cont][$aLabelGrid[16]] = '';
		$aDataGrid[$cont][$aLabelGrid[17]] = '<div align="center">
														<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
														title = "Presione aqui para Eliminar"
														style="cursor: hand !important; cursor: pointer !important;"
														onclick="javascript:xajax_elimina_detalle_ret(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ');"
														alt="Eliminar"
														align="bottom" />
													</div>';
		$aDataGrid[$cont][$aLabelGrid[18]] = $contd;


		// DIARIO
		$aDataDiar[$contd][$aLabelDiar[0]] = floatval($contd);
		$aDataDiar[$contd][$aLabelDiar[1]] = $cta_cre . $cta_deb;
		$aDataDiar[$contd][$aLabelDiar[2]] = $cuen_nom;
		$aDataDiar[$contd][$aLabelDiar[3]] = $doc;
		$aDataDiar[$contd][$aLabelDiar[4]] = $coti;

		if ($mone_cod == $mone_base) {
			// moneda local
			$cre_tmp = 0;
			$deb_tmp = 0;
			if ($coti > 0) {
				$cre_tmp = round(($val_cre / $coti), 2);
			}

			if ($coti > 0) {
				$deb_tmp = round(($val_deb / $coti), 2);
			}

			$aDataDiar[$contd][$aLabelDiar[5]] = $val_deb;
			$aDataDiar[$contd][$aLabelDiar[6]] = $val_cre;
			$aDataDiar[$contd][$aLabelDiar[7]] = $deb_tmp;
			$aDataDiar[$contd][$aLabelDiar[8]] = 0;
		} else {
			// moneda extra
			$aDataDiar[$contd][$aLabelDiar[5]] = $val_deb * $coti;
			$aDataDiar[$contd][$aLabelDiar[6]] = $val_cre * $coti;
			$aDataDiar[$contd][$aLabelDiar[7]] = $val_deb;
			$aDataDiar[$contd][$aLabelDiar[8]] = 0;
		}

		$vacio = '-1';
		$aDataDiar[$contd][$aLabelDiar[9]] = $det_ret;
		$aDataDiar[$contd][$aLabelDiar[10]] = '';
		$aDataDiar[$contd][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																	title = "Presione aqui para Eliminar"
																	style="cursor: hand !important; cursor: pointer !important;"
																	onclick="javascript:xajax_elimina_detalle_dir(' . $vacio . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', ' . $cont . '  );"
																	alt="Eliminar"
																	align="bottom" />';
		$aDataDiar[$contd][$aLabelDiar[12]] = '';
		$aDataDiar[$contd][$aLabelDiar[13]] = '';
		$aDataDiar[$contd][$aLabelDiar[14]] = '';
		$aDataDiar[$contd][$aLabelDiar[15]] = $cont;
	}

	// RETENCION
	$_SESSION['aDataGirdRet'] = $aDataGrid;
	$sHtml = mostrar_grid_ret($idempresa, $idsucursal);
	$oReturn->assign("divRet", "innerHTML", $sHtml);

	// DIARIO
	$sHtml = '';
	$_SESSION['aDataGirdDiar'] = $aDataDiar;
	$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
	$oReturn->assign("divDiario", "innerHTML", $sHtml);

	$oReturn->assign("cod_ret", "value", "");
	$oReturn->assign("fact_ret", "value", "");
	$oReturn->assign("ret_clpv", "value", "");
	$oReturn->assign("ret_porc", "value", "");
	$oReturn->assign("ret_base", "value", "");
	$oReturn->assign("valor_retenido", "value", "");
	$oReturn->assign("ret_num", "value", "");
	$oReturn->assign("origen", "value", date('Y/m/d'));

	// TOTAL DIARIO
	$oReturn->script("total_diario();");

	$oReturn->script("cerrar_ventana();");
	return $oReturn;
}

function mostrar_grid_ret($idempresa, $idsucursal)
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$aDataGrid  = $_SESSION['aDataGirdRet'];
	$aLabelGrid = array(
		'Fila', 			'Cta Ret', 				'Cliente', 			'Factura', 			'Ret Cliente', 				'Porc(%)', 				'Base Impo',
		'Valor', 			'N.- Retencion', 		'Detalle', 	 		'Origen', 			'Cotizacion',  				'Debito Moneda Local', 'Credito Monda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 	'Modificar', 		'Eliminar',			'DI'
	);


	$cont    = 0;
	$tot_cre = 0;
	$tot_deb = 0;
	foreach ($aDataGrid as $aValues) {
		$aux     = 0;
		foreach ($aValues as $aVal) {
			if ($aux == 0) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . ($cont + 1) . '</div>';
			} elseif ($aux == 2) {
				$sql = "select  clpv_nom_clpv from saeclpv where clpv_cod_clpv = $aVal and clpv_cod_empr = $idempresa ";
				$clpv_nom = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="left">' . $clpv_nom . '</div>';
			} elseif ($aux == 3) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="left">' . $aVal . '</div>';
			} elseif ($aux == 4) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="left">' . $aVal . '</div>';
			} elseif ($aux == 5) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
			} elseif ($aux == 6) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
			} elseif ($aux == 7) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
			} elseif ($aux == 8) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
			} elseif ($aux == 9) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="left">' . $aVal . '</div>';
			} elseif ($aux == 10) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . $aVal . '</div>';
			} elseif ($aux == 11) {	//  cotizacion	
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
			} elseif ($aux == 12) {		// debito local
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
				$tot_deb += $aVal;
			} elseif ($aux == 13) {		// credito local
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
				$tot_cre += $aVal;
			} elseif ($aux == 14) {		// debito extr
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
			} elseif ($aux == 15) {		// credito exy
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . number_format(round($aVal, 2), 2, '.', ',') . '</div>';
			} elseif ($aux == 16) {
				$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="center">
                                                                <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil.png"
                                                                title = "Presione aqui para Eliminar"
                                                                style="cursor: hand !important; cursor: pointer !important;"
                                                                onclick="javascript:xajax_elimina_detalle_ret(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ');"
                                                                alt="Eliminar"
                                                                align="bottom" />
                                                            </div>';
			} else
				$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
			$aux++;
		}
		$cont++;
	}

	$array = array('', '', '', '', '', '', '', '', '', '', '', $tot_deb, $tot_cre, '', '');
	//return 'assas'.count($aDatos);
	return genera_grid($aDatos,   $aLabelGrid,   'RETENCION',   99,   '', $array);
}

function elimina_detalle_ret($id = null, $idempresa, $idsucursal, $id_di)
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oReturn = new xajaxResponse();

	$aLabelGrid = array(
		'Fila', 			'Cta Ret', 				'Cliente', 				'Factura', 				'Ret Cliente',
		'Porc(%)', 			'Base Impo',        	'Valor', 				'N.- Retencion', 		'Detalle',
		'Origen',
		'Cotizacion',       'Debito Moneda Local', 	'Credito Moneda Local', 'Debito Moneda Ext', 	'Credito Moneda Ext',
		'Modificar', 		'Eliminar',				'DI'
	);

	$aDataGrid = $_SESSION['aDataGirdRet'];

	$contador = count($aDataGrid);
	if ($contador > 1) {
		unset($aDataGrid[$id]);
		$aDataGrid = array_values($aDataGrid);
		$cont 	   = 0;

		foreach ($aDataGrid as $aValues) {
			$aux     = 0;
			foreach ($aValues as $aVal) {
				if ($aux == 0) {
					$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . ($cont + 1) . '</div>';
				} elseif ($aux == 16) {
					$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="center">
																			<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil.png"
																			title = "Presione aqui para Eliminar"
																			style="cursor: hand !important; cursor: pointer !important;"
																			onclick="javascript:xajax_elimina_detalle_ret(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ');"
																			alt="Eliminar"
																			align="bottom" />
																		</div>';
				} else
					$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
				$aux++;
			}
			$cont++;
		}

		$_SESSION['aDataGirdRet'] = $aDatos;
		$sHtml = mostrar_grid_ret($idempresa, $idsucursal);
		$oReturn->assign("divRet", "innerHTML", $sHtml);
	} else {
		unset($aDataGrid[0]);
		$_SESSION['aDataGirdRet'] = $aDatos;
		$sHtml = "";
		$oReturn->assign("divRet", "innerHTML", $sHtml);
	}


	// DIARIO						
	$aLabelGrid = array(
		'Fila', 			 'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion', 			'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 	'Detalle',		'Modificar', 		'Eliminar',				'Centro Costo',   			'Centro Actividad',
		'DIR',				 'RET'
	);

	unset($aDataGrid);
	$contador   = 0;
	$aDataGrid  = $_SESSION['aDataGirdDiar'];
	$contador   = count($aDataGrid);
	unset($aDatos);
	if ($contador > 1) {
		unset($aDataGrid[$id_di]);
		$aDataGrid = array_values($aDataGrid);
		$cont = 0;
		foreach ($aDataGrid as $aValues) {
			$aux = 0;
			foreach ($aValues as $aVal) {
				if ($aux == 0) {
					$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="right">' . ($cont + 1) . '</div>';
				} elseif ($aux == 10) {
					$aDatos[$cont][$aLabelGrid[$aux]] = '<div align="center">
                                                                <img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/pencil.png"
                                                                title = "Presione aqui para Eliminar"
                                                                style="cursor: hand !important; cursor: pointer !important;"
                                                                onclick="javascript:xajax_elimina_detalle_dir(' . $cont . ', ' . $idempresa . ', ' . $idsucursal . ');"
                                                                alt="Eliminar"
                                                                align="bottom" />
                                                            </div>';
				} else
					$aDatos[$cont][$aLabelGrid[$aux]] = $aVal;
				$aux++;
			}
			$cont++;
		}

		$_SESSION['aDataGirdDiar'] = $aDatos;
		$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
		$oReturn->assign("divDiario", "innerHTML", $sHtml);
	} else {
		unset($aDataGrid[0]);
		$_SESSION['aDataGirdDiar'] = $aDatos;
		$sHtml = "";
		$oReturn->assign("divDiario", "innerHTML", $sHtml);
	}

	// TOTAL DIARIO
	$oReturn->script("total_diario();");

	return $oReturn;
}



function guardar($aForm = '', $factcheq = '', $lisfact = '', $codModu='')
{
	//Definiciones
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oIfxA = new Dbo;
	$oIfxA->DSN = $DSN_Ifx;
	$oIfxA->Conectar();

	$oReturn = new xajaxResponse();

	//variables de session
	$user_web     = $_SESSION['U_ID'];
	$user_ifx     = $_SESSION['U_USER_INFORMIX'];
	$aDataGrid    = $_SESSION['aDataGirdDir'];
	$aDataDiar    = $_SESSION['aDataGirdDiar'];
	$aDataGridRet = $_SESSION['aDataGirdRet'];

	//variables de formulario
	$moduloComp = 5;
	$tipoComp = 'DI';
	$idempresa    = $aForm['empresa'];
	$idsucursal   = $aForm['sucursal'];
	$tidu_cod     = $aForm['tipo_doc'];
	$fecha_mov    = $aForm['fecha'];
	$clpv_ruc     = $aForm['ruc'];
	$clpv_nom     = $aForm['cliente_nombre'];
	$clpv_cod     = $aForm['cliente'];
	$empl_cod     = $aForm['empleado'];
	$asto_val     = str_replace(",", "", $aForm['valor']);
	$form_cod     = $aForm['formato'];
	$deas         = $aForm['deas'];
	$deta_asto    = $aForm['detalle'];
	$asto_cod     = $aForm['asto_cod'];
	$comp_cod     = $aForm['compr_cod'];
	$time         = date("Y-m-d H:i:s");


	$debito_total     = $aForm['debito_total'];
	$credito_total     = $aForm['credito_total'];

	if (empty($clpv_cod)) {
		$clpv_cod = 0;
	}

	if (count($aDataDiar) > 0) {
		// TRANSACCIONALIDAD IFX
		try {

			//$Logs = new Logs(4);
			// commit
			$oIfx->QueryT('BEGIN WORK;');



			if ($debito_total != $credito_total) {
				throw new Exception('El asiento esta descuadrado');
			}


			//MAYORIZACION
			$class = new mayorizacion_class();
			unset($array);
			$sqlm = "select tidu_cod_modu from saetidu where tidu_cod_tidu = '$tidu_cod'";
			$codmodu = consulta_string($sqlm, 'tidu_cod_modu', $oIfx, 0);
			$moduloComp = $codmodu;
			$array = $class->secu_asto($oIfx, $idempresa, $idsucursal, $moduloComp, $fecha_mov, $user_ifx, $tidu_cod, $tipoComp);
			foreach ($array as $val) {
				$secu_asto  = $val[0];
				$secu_dia   = $val[1];
				$tidu       = $val[2];
				$idejer     = $val[3];
				$idprdo     = $val[4];
				$moneda     = $val[5];
				$tcambio    = $val[6];
				$empleado   = $val[7];
				$usua_nom   = $val[8];
			} // fin foreach     



			//---------------------------------------------------------------
			//		ACTUALIZACION DE ADJUNTOS (INGRESO DEL ASTO)			//
			//						SUEJO4967				   				//
			//---------------------------------------------------------------

			$sql = "UPDATE comercial.adjuntos set asto = '$secu_asto',
													id_ejer='$idejer',
													id_prdo='$idprdo',
													estado='A'	where
													estado = 'PE' and
													id_clpv = $clpv_cod ";

			$oIfx->QueryT($sql);

			$sql_delete = "DELETE from comercial.adjuntos where estado='PE'";
			$oIfx->QueryT($sql_delete);

			//---------------------------------------------------------------
			// 					FIN DE ACTUALIZACION DE ADJUNTOS		   //
			//---------------------------------------------------------------

			// SAEASTO
			$class->saeasto(
				$oIfx,
				$secu_asto,
				$idempresa,
				$idsucursal,
				$idejer,
				$idprdo,
				$moneda,
				$user_ifx,
				'',
				$clpv_nom,
				0,
				$fecha_mov,
				$deta_asto,
				$secu_dia,
				$fecha_mov,
				$tidu,
				$usua_nom,
				$user_web,
				$moduloComp,
				$tipoComp,
				$form_cod
			);

			// SAEDIR
			$x = 1;
			$j = 1;
			$cod_dir = 0;
			$moduloDire = null;
			if (count($aDataGrid) > 0) {
				foreach ($aDataGrid as $aValues) {
					$aux = 0;
					$total = 0;
					foreach ($aValues as $aVal) {
						if ($aux == 0) {
							// CONT
							$cod_dir++;
						} elseif ($aux == 1) {
							// CLPV COD
							$clpv_cod  = $aVal;
							$sql = "select  clpv_nom_clpv, clpv_clopv_clpv from saeclpv where clpv_cod_clpv = $clpv_cod and clpv_cod_empr = $idempresa ";
							if ($oIfx->Query($sql)) {
								if ($oIfx->NumFilas() > 0) {
									$clpv_nom = $oIfx->f('clpv_nom_clpv');
									$clpv_clopv_clpv = $oIfx->f('clpv_clopv_clpv');
								}
							}
							$oIfx->Free();

							if ($clpv_clopv_clpv == 'PV') {
								$moduloDire = 4;
							} elseif ($clpv_clopv_clpv == 'CL') {
								$moduloDire = 3;
							}
						} elseif ($aux == 2) {
							// CCLI
							$ccli = $aVal;
						} elseif ($aux == 3) {
							// TIPO
							$tipo = $aVal;
						} elseif ($aux == 4) {
							// FACTURA
							$factura  = $aVal;
						} elseif ($aux == 5) {
							// FECHA VENCE
							$fec_vence = ($aVal);
						} elseif ($aux == 6) {
							// DETALLE
							$detalle = $aVal;
						} elseif ($aux == 7) {
							// COTIZACION
							$cotiza = $aVal;
						} elseif ($aux == 8) {
							// DEBITO
							$debito = $aVal;
						} elseif ($aux == 9) {
							// CREDITO
							$credito = $aVal;
						} elseif ($aux == 10) {
							// DEBITO EXTR
							$debito_ext = $aVal;
						} elseif ($aux == 11) {
							// CREDITO EXT
							$credito_ext = $aVal;

							$class->saedir(
								$oIfx,
								$idempresa,
								$idsucursal,
								$idprdo,
								$idejer,
								$secu_asto,
								$clpv_cod,
								$moduloDire,
								$tipo,
								$factura,
								$fec_vence,
								$detalle,
								$debito,
								$credito,
								$debito_ext,
								$credito_ext,
								'CR',
								'',
								'',
								'',
								'',
								'',
								'',
								$user_web,
								$cod_dir,
								$cotiza,
								$clpv_nom,
								$ccli,
								$empl_cod
							);
						}
						$aux++;
					}
					$x++;
					$j++;
				} // fin foreach

			}

			// RET
			if (count($aDataGridRet) > 0) {
				$x = 1;
				$j = 1;
				foreach ($aDataGridRet as $aValues) {
					$aux = 0;
					$total = 0;
					foreach ($aValues as $aVal) {
						if ($aux == 0) {
							$ret_secu = $aVal + 1;
						} elseif ($aux == 1) {
							$cta_ret = $aVal;
						} elseif ($aux == 2) {
							$clpv_cod = $aVal;
							$sql = "select  clpv_nom_clpv, clpv_ruc_clpv from saeclpv where clpv_cod_clpv = $clpv_cod and clpv_cod_empr = $idempresa ";
							if ($oIfx->Query($sql)) {
								$clpv_nom = $oIfx->f('clpv_nom_clpv');
								$clpv_ruc = $oIfx->f('clpv_ruc_clpv');
							}

							$sql = "select dire_dir_dire from saedire where 
												dire_cod_empr = $idempresa and
												dire_cod_clpv = $clpv_cod ";
							$clpv_dir = consulta_string_func($sql, 'dire_dir_dire', $oIfx, '');
							$sql = "select emai_ema_emai from saeemai where
												emai_cod_empr = $idempresa  and
												emai_cod_clpv = $clpv_cod ";
							$clpv_correo = consulta_string_func($sql, 'emai_ema_emai', $oIfx, '');
						} elseif ($aux == 3) {
							$factura = $aVal;
						} elseif ($aux == 4) {
							$ret_clpv = $aVal;
						} elseif ($aux == 5) {
							$ret_porc = $aVal;
						} elseif ($aux == 6) {
							$ret_base = $aVal;
						} elseif ($aux == 7) {
							$ret_val = $aVal;
						} elseif ($aux == 8) {
							$ret_num = $aVal;
						} elseif ($aux == 9) {
							$ret_det = $aVal;
						} elseif ($aux == 10) {
							$origen = $aVal;
							if (!empty($origen)) {
								$origen = ($origen);
							}
						} elseif ($aux == 11) {
							$cotiza  = $aVal;
						} elseif ($aux == 12) {
							$debito  = $aVal;
						} elseif ($aux == 13) {
							$credito = $aVal;
						} elseif ($aux == 14) {
							$debito_ext  = $aVal;
						} elseif ($aux == 15) {
							$class->saeret(
								$oIfx,
								$idempresa,
								$idsucursal,
								$idprdo,
								$idejer,
								$secu_asto,
								$clpv_cod,
								$clpv_nom,
								$clpv_dir,
								'',
								$clpv_ruc,
								$ret_secu,
								$cta_ret,
								$ret_porc,
								$ret_base,
								$ret_val,
								$ret_num,
								$ret_det,
								$debito,
								$credito,
								$debito_ext,
								$credito_ext,
								$factura,
								'',
								'',
								'',
								$clpv_correo,
								'N',
								$origen,
								$cotiza
							);
						}
						$aux++;
					}
					$x++;
					$j++;
				} // fin foreach
			}


			// DIARIO
			//('Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion', 		'Debito Moneda Local', 		'Credito Moneda Local', 
			//			'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar', 		'Centro Costo', 			'Centro Actividad' );

			if (count($aDataDiar) > 0) {
				$x = 1;
				$j = 1;
				$total = 0;
				foreach ($aDataDiar as $aValues) {
					$aux = 0;
					foreach ($aValues as $aVal) {
						if ($aux == 0) {
							$dasi_cod = $aVal + 1;
						} elseif ($aux == 1) {
							$cta_cod = $aVal;
						} elseif ($aux == 2) {
							$cta_nom = $aVal;
						} elseif ($aux == 3) {
							$documento = $aVal;
						} elseif ($aux == 4) {
							$cotiza = $aVal;
						} elseif ($aux == 5) {
							$debito = $aVal;
						} elseif ($aux == 6) {
							$credito = $aVal;
							$total += $debito;
						} elseif ($aux == 7) {
							$debito_ext = $aVal;
						} elseif ($aux == 8) {
							$credito_ext = $aVal;
						} elseif ($aux == 9) {
							$det_dasi = $aVal;
						} elseif ($aux == 12) {
							$ccosn_cod = $aVal;
						} elseif ($aux == 13) {
							$act_cod = $aVal;

							// DASI
							$class->saedasi(
								$oIfx,
								$idempresa,
								$idsucursal,
								$cta_cod,
								$idprdo,
								$idejer,
								$ccosn_cod,
								$debito,
								$credito,
								$debito_ext,
								$credito_ext,
								$cotiza,
								$det_dasi,
								'',
								'',
								$user_web,
								$secu_asto,
								$dasi_cod_ret,
								$dasi_dir,
								'',
								$documento,
								$act_cod
							);
						}
						$aux++;
					}
					$x++;
					$j++;
				} // fin foreach
			} // fin




			// ACTUALIZACION SAEASTO
			$sql = "update saeasto set asto_est_asto = 'MY', asto_vat_asto = $total  
					where
					asto_cod_empr = $idempresa and
					asto_cod_sucu = $idsucursal and
					asto_cod_asto = '$secu_asto' and
					asto_cod_ejer = $idejer and
					asto_num_prdo = $idprdo and
					asto_cod_modu = $moduloComp and
					asto_user_web = $user_web ";
			$oIfx->QueryT($sql);

			$oReturn->assign("asto_cod", "value", $secu_asto);
			$oReturn->assign("compr_cod", "value", $secu_asto);

			// ASIENTOS 
			$oReturn->assign("asto_cod", "value", $secu_asto);
			$oReturn->assign("ejer_cod", "value", $idejer);
			$oReturn->assign("prdo_cod", "value", $idprdo);





			$importacion_modulos = $_SESSION['GESTION_IMPORTACION'];
			if (!empty($importacion_modulos)) {
				if (empty($idempresa)) {
					$idempresa = 0;
				}
				if (empty($idsucursal)) {
					$idsucursal = 0;
				}
				if (empty($idejer)) {
					$idejer = 0;
				}
				if (empty($idprdo)) {
					$idprdo = 0;
				}
				if (empty($clpv_cod)) {
					$clpv_cod = 0;
				}
				if (empty($secu_asto)) {
					$secu_asto = 0;
				}
				$descripcion_modulo = 'MODULO DIARIO CUENTAS X PAGAR SAEASTO SAEDASI';
				$id_movimiento_actualzar = $importacion_modulos;
				$estado = 'DIA';
				$tran = '021';
				$oReturn->script("actualizar_estado_gestion_importacion($id_movimiento_actualzar, $idempresa, $idsucursal, $idejer, $idprdo, $clpv_cod, '$secu_asto', '$tran', '$descripcion_modulo', '$estado')");
			}


			//VALIDAICION ESTADO SAERGFP CHEQUE PROTESTADOS
			if ($factcheq != 0) {

				$array_cheq = explode(':', $factcheq);
				$cheq_clpv  = $array_cheq[0];
				$cheq_asto  = $array_cheq[1];
				$cheq_ejer  = $array_cheq[2];
				$cheq_prdo  = $array_cheq[3];
				$cheq_fila  = $array_cheq[4];
				$cheq_num   = $array_cheq[5];
				$cheq_banc  = $array_cheq[6];
				$cheq_sucu  = $array_cheq[7];

				$sqlu = "update saergfp set cfpg_est_chp ='S', cfpg_asto_prot='$secu_asto', cfpg_prot_ejer=$idejer,  cfpg_prot_prdo=$idprdo
					where cfpg_cod_clpv= $cheq_clpv and 
					cfpg_cod_asto ='$cheq_asto' and cfpg_cod_ejer=$cheq_ejer and cfpg_num_prdo='$cheq_prdo' and  cfpg_con_fila=$cheq_fila
					and cfpg_num_cheq='$cheq_num' and cfpg_nom_banc = '$cheq_banc' and cfpg_cod_sucu= $cheq_sucu";
				$oIfx->QueryT($sqlu);

				//ACTUALIZACION REGISTROS FACTURAS SAEGEIN

				$array_fact = explode('/', $lisfact);

				/*foreach ($array_fact as $nfact) {


					if (!empty($nfact)) {

						//DATOS FACTURA

						$array_detfact = explode(':', $nfact);


						//$tipo = $array_detfact[2];
						$dfpag = $array_detfact[0];
						$fact_num = $array_detfact[1];
						$dempr = $array_detfact[2];
						$dsucu = $array_detfact[3];
						$dclpv = $array_detfact[4];
						$dasto = $array_detfact[5];
						$dejer = $array_detfact[6];
						$dprdo = $array_detfact[7];
						$dcheq = $array_detfact[8];
						$dbanc = $array_detfact[9];

						$sqlu = "update saegein set gein_est_chp ='S'where 
						gein_cod_fpag=$dfpag and gein_num_fact='$fact_num' and gein_cod_empr= $dempr and gein_cod_sucu=$dsucu 
						and gein_cod_clpv=$dclpv and gein_cod_asto ='$dasto' and gein_asto_ejer = $dejer and gein_asto_prdo=$dprdo
						and gein_num_cheq='$dcheq' and gein_nom_banc='$dbanc'";
						$oIfx->QueryT($sqlu);


						if ($tipo == 1) {
							$dempr = $array_detfact[3];
							$dsucu = $array_detfact[4];
							$dclpv = $array_detfact[5];
							$dasto = $array_detfact[6];
							$dejer = $array_detfact[7];
							$dfila = $array_detfact[8];

							$sqlu = "update saedpag set dpag_est_chp ='S'where dpag_num_fact='$fact_num' and dpag_cod_clpv=$dclpv
						and dpag_cod_empr=$dempr and dpag_cod_sucu=$dsucu and dpag_cod_asto='$dasto' and dpag_cod_ejer=$dejer
						and dpag_con_fila=$dfila";
							$oIfx->QueryT($sqlu);
						}
						if ($tipo == 2) {
							$codgein = $array_detfact[3];

							$sqlu = "update saegein set gein_est_chp ='S'where gein_cod_gein=$codgein";
							$oIfx->QueryT($sqlu);
						}
					}
				}*/
			}

			//ACTUALIZAICON ASIENTO INICIAL 

			if(!empty($codModu)){
				$sqla="update saeejer set ejer_asi_inic = '$secu_asto' where ejer_cod_ejer=$idejer and ejer_cod_empr=$idempresa";
				$oIfx->QueryT($sqla);
			}

			$oIfx->QueryT('COMMIT WORK;');
			$oReturn->alert('Ingresado Correctamente...');
		} catch (Exception $e) {
			// rollback
			$oIfx->QueryT('ROLLBACK WORK;');
			$oReturn->alert($e->getMessage());
			$oReturn->assign("ctrl", "value", 1);
			$oReturn->script('habilitar_boton();');
		}
	} else {
		$oReturn->alert('Por favor realice un Diario Contable....');
		$oReturn->script('habilitar_boton();');
	}

	return $oReturn;
}

function numero_ret($aForm = '', $op = 0)
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oReturn = new xajaxResponse();

	if ($op == 1) {
		$rete  = $aForm['ret_num'];
		$len   = strlen(($rete));
		$ceros = cero_mas_func('0', (9 - $len));
		$rete  = $ceros . $rete;

		$oReturn->assign("ret_num", "value", $rete);
	} elseif ($op == 2) {
		$rete  = $aForm['documento'];
		$len   = strlen(($rete));
		$ceros = cero_mas_func('0', (10 - $len));
		$rete  = $ceros . $rete;

		$oReturn->assign("documento", "value", $rete);
	}

	return $oReturn;
}

function total_diario($aForm = '')
{
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn    = new xajaxResponse();

	$aDataGrid  = $_SESSION['aDataGirdDiar'];

	$idempresa  = $aForm["empresa"];
	$mone_cod   = $aForm["moneda"];
	$sql        = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
	$mone_base  = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

	$cont        = 0;
	$tot_cre     = 0;
	$tot_deb     = 0;
	$tot_cre_ext = 0;
	$tot_deb_ext = 0;
	foreach ($aDataGrid as $aValues) {
		$aux = 0;
		foreach ($aValues as $aVal) {
			if ($aux == 5) {
				$tot_deb += $aVal;
				//$oReturn->alert('DB'.$tot_deb);
			} elseif ($aux == 6) {
				$tot_cre += $aVal;
				//$oReturn->alert('CR'.$tot_cre);
			} elseif ($aux == 7) {
				$tot_deb_ext += $aVal;
			} elseif ($aux == 8) {
				$tot_cre_ext += $aVal;
			}
			$aux++;
		}
		$cont++;
	}

	///$oReturn->alert('DB'.$tot_deb.'CR'.$tot_cre);
	//$oReturn->alert('moned  '.$mone_cod.' ext '.$mone_extr);

	$importacion_modulos = $_SESSION['GESTION_IMPORTACION'];
	if (empty($importacion_modulos)) {
		if ($mone_cod == $mone_base) {
			$oReturn->assign("val_cta", "value", round((abs($tot_cre - $tot_deb)), 2));
		} else {
			$oReturn->assign("val_cta", "value", round((abs($tot_cre_ext - $tot_deb_ext)), 2));
		}
	}
	$oReturn->assign("valor", "value", round((abs($tot_deb)), 2));

	$tot_cre = abs($tot_cre);
	$tot_deb = abs($tot_deb);
	$tipo = '';
	if ($tot_cre > $tot_deb) {
		$tipo = 'DB';
	} elseif ($tot_deb > $tot_cre) {
		$tipo = 'CR';
	}

	$oReturn->assign("crdb", "value",  $tipo);











	return $oReturn;
}


function cargar_lista_subcliente($aForm = '')
{
	//Definiciones
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	//variables de session
	$idempresa = $_SESSION['U_EMPRESA'];

	//variables del formulario
	$empresa = $aForm['empresa'];
	$cliente = $aForm['clpv_cod'];

	$sql = "select ccli_cod_ccli, ccli_nom_conta  
			from saeccli where
			ccli_cod_empr = $empresa and 
			ccli_cod_clpv = $cliente";
	//$oReturn->alert($sql);
	$i = 1;
	if ($oIfx->Query($sql)) {
		$oReturn->script('eliminar_lista_subcliente();');
		if ($oIfx->NumFilas() > 0) {
			do {
				$oReturn->script(('anadir_elemento_subcliente(' . $i++ . ',\'' . $oIfx->f('ccli_cod_ccli') . '\', \'' . $oIfx->f('ccli_nom_conta') . '\' )'));
			} while ($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();

	return $oReturn;
}

function calculaValorRetenido($aForm = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oReturn = new xajaxResponse();

	//variables del formulario
	$porc_ret = $aForm['ret_porc'];
	$base_ret = $aForm['ret_base'];

	if ($porc_ret > 0 && $base_ret > 0) {
		$val_ret = round(($base_ret * $porc_ret / 100), 2);
	}

	$oReturn->assign("valor_retenido", "value", $val_ret);

	return $oReturn;
}

function valorSaldo($aForm = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oReturn = new xajaxResponse();

	//variables del formulario
	$arraySaldo = $_SESSION['ARRAY_SALDOS_TMP'];

	if (count($arraySaldo)) {
		foreach ($arraySaldo as $val) {
			$tipoCuenta = $val[0];
			$valSaldo = abs($val[1]);
		}
		$oReturn->assign("val_cta", "value", $valSaldo);
		$oReturn->assign("crdb", "value", $tipoCuenta);
	}

	return $oReturn;
}



function cargar_coti($aForm = '')
{
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	//variables del formulario
	$idempresa = $aForm['empresa'];
	$mone_cod  = $aForm['moneda'];

	$sql = "select tcam_val_tcam from saetcam where
				mone_cod_empr = $idempresa and
				tcam_cod_mone = $mone_cod and
				tcam_fec_tcam in (
									select max(tcam_fec_tcam)  from saetcam where
											mone_cod_empr = $idempresa and
											tcam_cod_mone = $mone_cod
								)  ";

	$coti = consulta_string($sql, 'tcam_val_tcam', $oIfx, 0);


	$oReturn->assign("cotizacion", "value", $coti);
	$oReturn->assign("cotizacion_ret", "value", $coti);
	return $oReturn;
}




// PRESTAMOS EMPLEADOS
function formulario_prestamo($idempresa = '', $idsucursal = '', $empl_cod, $detalle)
{
	//  Definiciones
	global $DSN_Ifx, $DSN;
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$ifu = new Formulario;
	$ifu->DSN = $DSN_Ifx;

	$fu = new Formulario;
	$fu->DSN = $DSN;

	$oReturn      = new xajaxResponse();

	$sAccion = 'nuevo';

	switch ($sAccion) {
		case 'nuevo':
			$ifu->AgregarCampoFecha('fecha_pres', 'Fecha|left', true, date('Y') . '/' . date('m') . '/' . date('d'));
			$ifu->AgregarCampoListaSQL('empleado_pres', 'Empleado|left', "select empl_cod_empl, empl_ape_nomb, * from saeempl where
																						empl_cod_empr = $idempresa and
																						empl_cod_eemp = 'A' and empl_cod_empl='$empl_cod'
																						order by 3 ", true, 200, 200);
			$ifu->AgregarCampoNumerico('codigo_pres', 'Codigo|left', true, 0, 80, 150);
			$ifu->AgregarCampoTexto('detalle_pres', 'Concepto|left', true, $detalle, 400, 200);


			$ifu->AgregarCampoListaSQL('tipo_pres', 'Tipo Prestamos|left', "SELECT tpre_cod_tpre, tpre_des_tpre from saetpre where tpre_cod_empr = $idempresa ", true, 200, 150);
			$ifu->AgregarCampoNumerico('monto', 'Monto|left', true, 0, 70, 150);
			$ifu->AgregarCampoNumerico('plazo', 'Plazo (Meses)|left', true, 0, 70, 150);
			$ifu->AgregarCampoNumerico('interes', 'Tasa Interes|left', true, 0, 70, 150);

			$anio = date("Y");
			$mes  = date("m");

			$ifu->AgregarCampoListaSQL('mes_pres', 'Mes Pago|left', "select prdo_num_prdo, prdo_nom_prdo from saeprdo where prdo_cod_empr = $idempresa group by 1,2 order by 1  ", true, 100, 150);
			$ifu->AgregarCampoListaSQL('anio_pres', 'Anio Pago|left', "select DATE_PART('year', ejer_fec_inil) as id_anio, DATE_PART('year', ejer_fec_inil) as id_anio  from saeejer where ejer_cod_empr = $idempresa order by 1 desc ", true, 100, 150);


			$ifu->cCampos["anio_pres"]->xValor      = $anio;
			$ifu->cCampos["mes_pres"]->xValor       = $mes;
			$ifu->cCampos["empleado_pres"]->xValor  = $empl_cod;

			break;
	}

	$sHtml .= '<table class="table table-striped table-condensed" style="margin-bottom: 0px; width: 95%" align="center">
					<tr>
						<td>
							<div class="btn btn-primary btn-sm" onclick="genera_formulario();">
                                <span class="glyphicon glyphicon-file"></span>
                                Nuevo
                            </div>
							<div class="btn btn-primary btn-sm" onclick="guardar_prestamo();">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                Guardar
                            </div>
							<div class="btn btn-primary btn-sm" onclick="vista_previa();">
                                <span class="glyphicon glyphicon-print"></span>
                                Imprimir
                            </div>
						</td>
						<td align="right">
							<div class="btn btn-danger btn-sm" onclick="cancelar_pedido();">
                                <span class="glyphicon glyphicon-remove"></span>
                                Cancelar
                            </div>
						</td>
					</tr>
                  </table>';
	$sHtml .= '<table class="table table-striped table-condensed" style="margin-bottom: 0px; width: 95%" align="center">';
	$sHtml .= '<tr>
                       <td class="bg-primary" align="center" colspan="4">PRESTAMOS</td>
                   </tr>';
	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('fecha_pres') . '</td>
						<td colspan="3">' . $ifu->ObjetoHtml('fecha_pres') . '</td>
						
					</tr>';
	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('empleado_pres') . '</td>
						<td colspan="3">' . $ifu->ObjetoHtml('empleado_pres') . '</td>
                   </tr>';
	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('detalle_pres') . '</td>
						<td colspan="3">' . $ifu->ObjetoHtml('detalle_pres') . '</td>
                   </tr>';
	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('tipo_pres') . '</td>
						<td>' . $ifu->ObjetoHtml('tipo_pres') . '</td>     
						<td>' . $ifu->ObjetoHtmlLBL('monto') . '</td>
						<td>' . $ifu->ObjetoHtml('monto') . '</td>     
                   </tr>';
	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('plazo') . '</td>
						<td>' . $ifu->ObjetoHtml('plazo') . '</td>     
						<td>' . $ifu->ObjetoHtmlLBL('interes') . '</td>
						<td>' . $ifu->ObjetoHtml('interes') . '</td>     
                   </tr>';

	$sHtml .= '<tr>
						<td>' . $ifu->ObjetoHtmlLBL('anio_pres') . '</td>
						<td>' . $ifu->ObjetoHtml('anio_pres') . '</td>     
						<td>' . $ifu->ObjetoHtmlLBL('mes_pres') . '</td>
						<td>' . $ifu->ObjetoHtml('mes_pres') . '</td>     
                   </tr>';
	$sHtml .= '<tr>
                        <td align="center" colspan="4">
							<div class="btn btn-primary btn-sm" onclick="generarTablaAmortizacion();">
								<span class="glyphicon glyphicon-list-alt"></span>
								Generar
							</div>
						</td>
				</tr>';


	$sHtml .= '</table>';




	$oReturn->assign("divFormularioDetalle", "innerHTML", $sHtml);

	return $oReturn;
}


function generarTablaAmortizacion($aForm = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx, $DSN;

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	unset($_SESSION['ARRAY_TABLA_AMORTIZACION']);
	$idempresa = $_SESSION['U_EMPRESA'];

	$montoInicial 	= $aForm['monto'];
	$monto 			= $aForm['monto'];
	$plazo 			= $aForm['plazo'];
	$interes 		= $aForm['interes'];
	$fecha 			= $aForm['fecha'];
	$empleado_pres  = $aForm['empleado_pres'];
	$detalle_pres  	= $aForm['detalle_pres'];
	$anio_pres  	= $aForm['anio_pres'];
	$fecha_pres  	= $aForm['fecha_pres'];
	$mes_pres		= $aForm['mes_pres'] -  1;
	$mes_pres2		= $aForm['mes_pres'];
	$sql = "select mes from mes where codigo=$mes_pres2	";
	$mes_pres2 = consulta_string($sql, 'mes', $oCon, '');
	$sql = "select empl_cod_empl, empl_ape_nomb, * from saeempl where
					empl_cod_empr = $idempresa and
					empl_cod_eemp = 'A' and empl_cod_empl='$empleado_pres'
					order by 3";

	$nombre = consulta_string($sql, 'empl_ape_nomb', $oIfx, '');
	if (!empty($interes)) {
		$interes 		= ($interes / 100) / 12;
	}

	if ($interes == 0) {
		$cuota 			= round(($monto / $plazo), 2);
	} else {
		$cuota 			= ($monto * $interes * (pow((1 + $interes), ($plazo)))) / ((pow((1 + $interes), ($plazo))) - 1);
	}




	try {
		$sHtml .= '<table class="table table-bordered table-hover table-striped table-condensed" style="width: 80%; margin-bottom: 1px; border:1px solid black; border-radius: 5px; margin-top:10px" align="center">';
		$sHtml .= '<tr>';
		$sHtml .= '<td style="width:100%;font-size:15px; text-align: center; border-right:0px solid; border-bottom:1px solid " colspan="6" align="center">TABLA DE AMORTIZACION</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr >';
		$sHtml .= '<td  style="width:5%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid ">No.</td>';
		$sHtml .= '<td  style="width:15%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid ">FECHA</td>';
		$sHtml .= '<td  style="width:15%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid ">CUOTA</td>';
		$sHtml .= '<td  style="width:15%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid ">INTERES</td>';
		$sHtml .= '<td  style="width:15%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid ">AMORTIZACION</td>';
		$sHtml .= '<td  style="width:15%;font-size:12px; text-align: left; border-right:0px solid; border-bottom:1px solid ">SALDO</td>';
		$sHtml .= '</tr>';
		unset($array);
		$totalInteres = 0;
		for ($i = 1; $i <= $plazo; $i++) {

			/*$nuevafecha = strtotime('+30 day', strtotime($fecha)) ;
				$nuevafecha = date ('Y/m/d', $nuevafecha);
				*/

			$day = date("d", mktime(0, 0, 0, $mes_pres + 1 + $i, 0, $anio_pres));
			$nuevafecha = date('Y/m/d', mktime(0, 0, 0, ($mes_pres + $i), $day, $anio_pres));

			$interesCalculado = $monto * $interes;
			$amortizacion = $cuota - $interesCalculado;

			$sHtml .= '<tr>';
			$sHtml .= '<td style="width:5%;font-size:12px;  text-align: right; border-right:1px solid; border-bottom:1px solid " align="right">' . $i . '</td>';
			$sHtml .= '<td style="width:15%;font-size:12px; text-align: left; border-right:1px solid; border-bottom:1px solid " align="left">' . $nuevafecha . '</td>';
			$sHtml .= '<td style="width:15%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid " align="right">' . number_format($cuota, 2, '.', ',') . '</td>';
			$sHtml .= '<td style="width:15%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid "align="right">' . number_format($interesCalculado, 2, '.', ',') . '</td>';
			$sHtml .= '<td style="width:15%;font-size:12px; text-align: right; border-right:1px solid; border-bottom:1px solid " align="right">' . number_format($amortizacion, 2, '.', ',') . '</td>';

			$monto = $monto - ($cuota - ($monto * $interes));

			$array[] = array($i, $cuota, $interesCalculado, $amortizacion, $monto, $nuevafecha);

			$fecha = $nuevafecha;

			$sHtml .= '<td style="width:15%;font-size:12px; text-align: right; border-right:0px solid; border-bottom:1px solid " align="right">' . number_format($monto, 2, '.', ',') . '</td>';
			$sHtml .= '</tr>';



			$totalInteres += $interesCalculado;
		}

		$granTotal = $montoInicial + $totalInteres;

		$sHtml .= '<tr>';
		$sHtml .= '<td colspan="6" class="bg-danger fecha_letra" align="right">Cuota Fija: ' . number_format($cuota, 2, '.', ',') . '</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td colspan="6" class="bg-danger fecha_letra" align="right">Interes Total: ' . number_format($totalInteres, 2, '.', ',') . '</td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td colspan="6" class="bg-danger fecha_letra" align="right">Total A Pagar: ' . number_format($granTotal, 2, '.', ',') . '</td>';
		$sHtml .= '</tr>';
		$sHtml .= '</table>';

		$_SESSION['ARRAY_TABLA_AMORTIZACION'] = $array;

		$sql = "select empr_ruc_empr, empr_dir_empr, empr_nom_empr, empr_path_logo from saeempr where empr_cod_empr = $idempresa ";
		if ($oIfx->Query($sql)) {
			$empr_ruc = $oIfx->f('empr_ruc_empr');
			$empr_dir = $oIfx->f('empr_dir_empr');
			$empr_nom = $oIfx->f('empr_nom_empr');
			$empr_path_logo = $oIfx->f('empr_path_logo');
			$empr_nom .= ' ';
		}
		$html = '<table border="0" style="width: 100%; margin-left:50px; margin-top:30px">
				<tr>
				    <td style="font-size:18px; text-align: left"><img src="' . $empr_path_logo . '" width="80""><br></td>
				</tr>
				<tr>
					<td style="width: 100%; font-size:18px; text-align: center"><strong>' . $empr_nom . '</strong></td>
				</tr>
				<tr>
					<td  style="width: 100%; font-size:14px; text-align: center"><strong>DIRECCION:' . $empr_dir . '</strong></td>
				</tr>
				<tr>
					<td style="width: 100%; font-size:14px; text-align: center"><strong>RUC:' . $empr_ruc . '</strong><br><br></td>
				</tr>
				<tr>
					<td style="width: 100%; font-size:14px; text-align: left"><strong>EMPLEADO:</strong>' . $empleado_pres . ' ' . $nombre . '</td>
				</tr>
				<tr>
					<td style="width: 100%; font-size:14px; text-align: left"><strong>CONCEPTO:</strong>' . $detalle_pres . '</td>
				</tr>
				<tr>
					<td style="width: 100%; font-size:14px; text-align: left"><strong>FECHA PRESTAMO:</strong>' . $fecha_pres . '</td>
				</tr>
				<tr>
					<td style="width: 100%; font-size:14px; text-align: left"><strong>MES INICIA PAGO:</strong> ' . $mes_pres2 . '</td>
				</tr>
				</table>';
		$html2 .= '<table style="width:100%; margin-top: 100px" align="center" >
					
					<tr>
						<td style="width:10%"></td>
				    <td style="width:10%; font-size:12px; text-align: center;border-top : 2px solid black;">Ingresado por:<br>' . $usuario . '</td>
						<td style="width:10%;"></td>
						<td style="width:10%;font-size:12px; text-align: center;border-top : 2px solid black;">Aprobado por</td> 
						<td style="width:10%;"></td>	
						<td style="width:10%; font-size:12px; text-align: center;border-top : 2px solid black;">Revisado por:</td> 
						<td style="width:10%;"></td>
						<td style="width:10%; font-size:12px; text-align: center;border-top : 2px solid black;">Recibí Conforme</td> 
						<td style="width:10%;"></td>
					</tr> 
					
					
			</table>';
		$_SESSION['pdf'] = $html . $sHtml . $html2;

		$oReturn->assign("divTablaAmortizacion", "innerHTML", $sHtml);
		$oReturn->assign("cuota", "value", $cuota);
		$oReturn->assign("interesTotal", "value", $totalInteres);
		$oReturn->assign("total", "value", $granTotal);
	} catch (Exception $e) {
		// rollback
		$oReturn->alert($e->getMessage());
	}


	return $oReturn;
}


function guardar_prestamo($id_empresa, $id_sucursal, $aForm = '')
{
	//Definiciones
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oIfxA = new Dbo;
	$oIfxA->DSN = $DSN_Ifx;
	$oIfxA->Conectar();

	$oReturn = new xajaxResponse();

	//variables de session
	$user_web     = $_SESSION['U_ID'];
	$user_ifx     = $_SESSION['U_USER_INFORMIX'];
	$array        = $_SESSION['ARRAY_TABLA_AMORTIZACION'];

	//variables de formulario
	$monto 			= $aForm['monto'];
	$plazo 			= $aForm['plazo'];
	$interes 		= $aForm['interes'];
	$fecha 			= $aForm['fecha'];
	$anio_pres  	= $aForm['anio_pres'];
	$mes_pres		= $aForm['mes_pres'];
	$empleado_pres  = $aForm['empleado_pres'];
	$tipo_pres      = $aForm['tipo_pres'];
	$fecha_pres     = ($aForm['fecha_pres']);
	$detalle_pres	= $aForm['detalle_pres'];

	if (count($array) > 0) {
		// TRANSACCIONALIDAD IFX
		try {
			// commit
			$oIfx->QueryT('BEGIN WORK;');

			$sql 		= "select max(pret_pre_impr) as cont from saepret where pret_cod_empr = $id_empresa ";
			$cod_pret 	=  consulta_string_func($sql, 'cont', $oIfx, 0);
			$cod_pret   = $cod_pret + 1;

			$serial     = '0101000' . $cod_pret;

			$sql = "insert into saepret (pret_cod_pret, 	pret_pre_impr, 		pret_cod_empl,		pret_cod_tpre,
										 pret_cod_tcuo,		pret_fec_pret,		pret_mot_pret, 		pret_num_cuot, 
										 pret_tas_pret,		pret_con_pret,      pret_cod_empr )
								values ( '$serial',    		$cod_pret,			'$empleado_pres',	$tipo_pres,
										 'P',				'$fecha_pres',		 $monto,				$plazo,
										 $interes,			'$detalle_pres',    $id_empresa ) ";
			$oIfx->QueryT($sql);

			foreach ($array as $val) {
				$i 					= $val[0];
				$cuota 				= $val[1];
				$interesCalculado	= $val[2];
				$amortizacion		= $val[3];
				$monto				= $val[4];
				$nuevafecha			= ($val[5]);

				$sql = "insert into saecuot ( cuot_num_cuot,		cuot_cod_pret,		cuot_mot_capi,		
											  cuot_est_cuot,		cuot_mot_inte,		cuot_fec_venc) 
									values ( $i,					'$serial',		    $cuota,
											 '0',					$interesCalculado,	'$nuevafecha' ) ";
				$oIfx->QueryT($sql);
			}


			$oIfx->QueryT('COMMIT WORK;');
			$oReturn->alert('Ingresado Correctamente...');

			$oReturn->script('generar_dasi();');
		} catch (Exception $e) {
			// rollback
			$oIfx->QueryT('ROLLBACK WORK;');
			$oReturn->alert($e->getMessage());
			$oReturn->assign("ctrl", "value", 1);
		}
	} else {
		$oReturn->alert('Por favor realice un Diario Contable....');
	}

	return $oReturn;
}


function agrega_modifica_grid_dia_empl($nTipo = 0, $aForm = '', $idempresa = '', $idsucursal, $detalle, $mone_cod, $coti, $coti_ext)
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$aDataDiar = $_SESSION['aDataGirdDiar'];

	$aLabelDiar = array(
		'Fila', 				'Cuenta', 					'Nombre', 				'Documento', 			'Cotizacion',
		'Debito Moneda Local', 	'Credito Moneda Local', 	'Debito Moneda Ext', 	'Credito Moneda Ext',	'Modificar',
		'Eliminar',             'Beneficiario',   			'Cuenta Bancaria',     	'Cheque',     			'Fecha Venc',
		'Formato Cheque', 		'Codigo Ctab', 				'Detalle',				'Centro Costo',   		'Centro Actividad'
	);

	$oReturn = new xajaxResponse();

	// VARIABLES
	$empl_cod     = $aForm["empleado_pres"];
	$tipo_presta  = $aForm["tipo_pres"];

	$sql = "select esem_cod_estr  from saeesem where
					esem_cod_empr = $idempresa and
					esem_cod_empl = '$empl_cod' ";
	$esem_cod_estr    = consulta_string_func($sql, 'esem_cod_estr', $oIfx, '');

	$sql = "select estr_cod_padr from saeestr where 
				estr_cod_empr = $idempresa and
				estr_cod_estr = '$esem_cod_estr' ";
	$estr_cod_padr    = consulta_string_func($sql, 'estr_cod_padr', $oIfx, '');

	$sql = "SELECT tpre_cod_tpre, tpre_des_tpre,  tpre_cod_rubr,  *  from saetpre where tpre_cod_empr = $idempresa ";
	$tpre_cod_rubr    = consulta_string_func($sql, 'tpre_cod_rubr', $oIfx, '');

	$sql = "select  cxru_cod_cuen from saecxru where
				cxru_cod_empr = $idempresa and
				cxru_cod_rubr = '$tpre_cod_rubr'  and
				cxru_cod_estr = '$estr_cod_padr' ";
	$cod_cta    = consulta_string_func($sql, 'cxru_cod_cuen', $oIfx, '');

	$sql = "select  cuen_nom_cuen  from saecuen where
                cuen_cod_empr  = $idempresa and
                cuen_cod_cuen  = '$cod_cta' ";
	$nom_cta    = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');
	$val_cta    = $aForm["monto"];

	$sql        = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
	$mone_base  = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

	if ($mone_cod == $mone_base) {
		$sql      = "select pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa ";
		$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

		$sql = "select tcam_val_tcam from saetcam where
                mone_cod_empr = $idempresa and
                tcam_cod_mone = $mone_extr and
                tcam_fec_tcam in (
                                    select max(tcam_fec_tcam)  from saetcam where
                                            mone_cod_empr = $idempresa and
                                            tcam_cod_mone = $mone_extr
                                )  ";

		$coti = $coti_ext; //consulta_string($sql, 'tcam_val_tcam', $oIfx, 0);
	}

	if ($nTipo == 0) {
		// DIARIO
		$cred = 0;
		$deb  = $val_cta;

		$contd = count($aDataDiar);
		$aDataDiar[$contd][$aLabelDiar[0]] = floatval($contd);
		$aDataDiar[$contd][$aLabelDiar[1]] = $cod_cta;
		$aDataDiar[$contd][$aLabelDiar[2]] = $nom_cta;
		$aDataDiar[$contd][$aLabelDiar[3]] = '';
		$aDataDiar[$contd][$aLabelDiar[4]] = $coti;

		if ($mone_cod == $mone_base) {
			// moneda local
			$cre_tmp = 0;
			$deb_tmp = 0;
			if ($coti > 0) {
				$cre_tmp = round(($cred / $coti), 2);
			}

			if ($coti > 0) {
				$deb_tmp = round(($deb / $coti), 2);
			}

			$aDataDiar[$contd][$aLabelDiar[5]] = $deb;
			$aDataDiar[$contd][$aLabelDiar[6]] = $cred;
			$aDataDiar[$contd][$aLabelDiar[7]] = $deb_tmp;
			$aDataDiar[$contd][$aLabelDiar[8]] = $cre_tmp;
		} else {
			// moneda extra         
			$aDataDiar[$contd][$aLabelDiar[5]] = $deb * $coti;
			$aDataDiar[$contd][$aLabelDiar[6]] = $cred * $coti;
			$aDataDiar[$contd][$aLabelDiar[7]] = $deb;
			$aDataDiar[$contd][$aLabelDiar[8]] = $cred;
		}

		$vacio = -1;
		$aDataDiar[$contd][$aLabelDiar[9]] = '';
		$aDataDiar[$contd][$aLabelDiar[10]] = '';
		$aDataDiar[$contd][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
													title = "Presione aqui para Eliminar"
													style="cursor: hand !important; cursor: pointer !important;"
													onclick="javascript:xajax_elimina_detalle_dir(' . $vacio . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
													alt="Eliminar"
													align="bottom" />';
		$aDataDiar[$contd][$aLabelDiar[12]] = '';
		$aDataDiar[$contd][$aLabelDiar[13]] = '';
		$aDataDiar[$contd][$aLabelDiar[14]] = '';
		$aDataDiar[$contd][$aLabelDiar[15]] = '';
		$aDataDiar[$contd][$aLabelDiar[16]] = '';
		$aDataDiar[$contd][$aLabelDiar[17]] = $detalle;
		$aDataDiar[$contd][$aLabelDiar[18]] = '';
		$aDataDiar[$contd][$aLabelDiar[19]] = '';
	}

	// DIARIO
	$sHtml = '';
	$_SESSION['aDataGirdDiar'] = $aDataDiar;
	$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
	$oReturn->assign("divDiario", "innerHTML", $sHtml);

	// TOTAL DIARIO
	$oReturn->script("total_diario();");

	$oReturn->script("cerrar_ventana();");
	return $oReturn;
}



// TRANSACCIONES
function cargar_lista_tran($aForm = '', $op)
{
	//Definiciones
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	//variables del formulario
	$empresa  = $aForm['empresa'];
	$sucursal = $aForm['sucursal'];
	$modulo   = null;

	if ($op == 'CL') {
		$modulo = 3;
	} elseif ($op == 'PV') {
		$modulo = 4;
	}

	$sql = "select tran_cod_tran, tran_des_tran, trans_tip_tran 
			from saetran where
			tran_cod_empr = $empresa and
			tran_cod_sucu = $sucursal and
			tran_cod_modu = $modulo 
			order by 2";
	$i = 1;
	if ($oIfx->Query($sql)) {
		$oReturn->script('eliminar_lista_tran();');
		if ($oIfx->NumFilas() > 0) {
			do {
				$detalle =  $oIfx->f('tran_cod_tran') . ' || ' . $oIfx->f('tran_des_tran') . ' || ' . $oIfx->f('trans_tip_tran');
				$oReturn->script(('anadir_elemento_tran(' . $i++ . ',\'' . $oIfx->f('tran_cod_tran') . '\',  \'' . $detalle . '\' )'));
			} while ($oIfx->SiguienteRegistro());
		}
	}
	$oIfx->Free();






	$importacion_modulos = $_SESSION['GESTION_IMPORTACION'];
	if (!empty($importacion_modulos)) {
		$sql_saeinfimp = "SELECT * from saeinfimp where infimp_cod_infimp = $importacion_modulos ";
		$infimp_cod_clpv = consulta_string($sql_saeinfimp, 'infimp_cod_clpv', $oIfx, 0);
		$infimp_fact_prove = consulta_string($sql_saeinfimp, 'infimp_fact_prove', $oIfx, 0);
		$infimp_val_infimp = consulta_string($sql_saeinfimp, 'infimp_val_infimp', $oIfx, 0);
		$infimp_val_iva = consulta_string($sql_saeinfimp, 'infimp_val_iva', $oIfx, 0);
		$infimp_cod_import = consulta_string($sql_saeinfimp, 'infimp_cod_import', $oIfx, 0);

		// ----------------------------------------------------------------------------------
		// Diario
		// ----------------------------------------------------------------------------------

		$sql_parametro_tran = "SELECT pccp_cod_facp FROM saepccp where pccp_cod_empr = $empresa";
		$pccp_cod_facp = consulta_string($sql_parametro_tran, 'pccp_cod_facp', $oIfx, '');

		$oReturn->assign("tran", "value", $pccp_cod_facp);
		$oReturn->assign("det_dir", "value", 'IMPORTACION');
		$oReturn->assign("factura", "value", $infimp_fact_prove);
		$oReturn->assign("fact_valor", "value", round($infimp_val_infimp + $infimp_val_iva, 2));
		$oReturn->script("anadir_dir()");


		// ----------------------------------------------------------------------------------
		// FIN Diario
		// ----------------------------------------------------------------------------------

	}




	return $oReturn;
}


// CONTROL DE EJERCICIO
function controlPeriodoIfx($aForm = '')
{
	global $DSN_Ifx, $DSN;

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	//variables del formulario
	$empresa = $aForm['empresa'];

	try {

		//control periodo
		$mesForm  = substr($aForm['fecha'], 5, 2);
		$anioForm = substr($aForm['fecha'], 0, 4);
		$fech = ($aForm['fecha']);
		$controlEjercicio = controlEjercicio($empresa, $anioForm);

		if ($controlEjercicio > 0) {

			$controlPeriodo = controlPeriodo($empresa, $anioForm, $mesForm);

			if ($controlPeriodo == 'C') {
				$oReturn->assign('fecha', 'value', date('d-m-Y'));
				// Cambio realizado por adrian
				$oReturn->alert('Mes Cerrado Consulte con el Administrador y Seleccione nuevamente Fecha Registro Contable');
			}
		} else {
			$oReturn->alert('No existe Ejercicio, Consulte con el Administrador...');
			$oReturn->assign('fecha', 'value', '');
		}
		// COTIZACION MONEDA EXTRANJERA
		$sql      = "select pcon_mon_base, pcon_seg_mone from saepcon where pcon_cod_empr = $empresa ";
		$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');
		$sql = "select tcam_valc_tcam from saetcam where
					mone_cod_empr = $empresa and
					tcam_cod_mone = $mone_extr and
					tcam_fec_tcam in (
										select max(tcam_fec_tcam)  from saetcam where
												mone_cod_empr = $empresa and
												tcam_cod_mone = $mone_extr and tcam_fec_tcam<='$fech'
									)  ";
		$coti = consulta_string($sql, 'tcam_valc_tcam', $oIfx, 0);
		$oReturn->assign("cotizacion_ext", "value", $coti);
	} catch (Exception $e) {
		$oReturn->alert($e->getMessage());
	}

	return $oReturn;
}




// MODIFICAR VALOR
function form_modificar_valor($id, $idempresa, $idsucursal, $aForm = '')
{

	global $DSN, $DSN_Ifx;
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo();
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$fu = new Formulario();
	$fu->DSN = $DSN;

	$oReturn = new xajaxResponse();

	$mone_cod = $aForm['moneda'];
	$sql      = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
	$mone_base = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

	$aDataGrid  = $_SESSION['aDataGirdDiar'];

	/*	
	'Debito Moneda Local', 		'Credito Moneda Local', 
	'Debito Moneda Ext', 'Credito Moneda Ext',
	*/
	$opcion = 0;
	$title  = '';
	if ($mone_cod != $mone_base) {
		// MONEDA LOCAL
		$debito  = $aDataGrid[$id]['Debito Moneda Local'];
		$credito = $aDataGrid[$id]['Credito Moneda Local'];
		$opcion  = 1;
		$title   = 'MONEDA LOCAL';
	} else {
		// MONEDA EXTRANJERA
		$debito  = $aDataGrid[$id]['Debito Moneda Ext'];
		$credito = $aDataGrid[$id]['Credito Moneda Ext'];
		$opcion  = 2;
		$title   = 'MONEDA EXTRANJERA';
	}

	$fu->AgregarCampoNumerico('debito_mod',  'Debito|left', false, $debito,  100, 100);
	$fu->AgregarCampoNumerico('credito_mod', 'Credito|left', false, $credito, 100, 100);

	$sHtml .= '<table class="table table-striped table-condensed" style="width: 90%; margin-bottom: 0px;" align="center">';
	$sHtml .= '<tr height="20">';
	$sHtml .= '<td>' . $fu->ObjetoHtmlLBL('debito_mod') . '</td>';
	$sHtml .= '<td>' . $fu->ObjetoHtml('debito_mod') . '</td>';
	$sHtml .= '</tr>';
	$sHtml .= '<tr height="20">';
	$sHtml .= '<td>' . $fu->ObjetoHtmlLBL('credito_mod') . '</td>';
	$sHtml .= '<td>' . $fu->ObjetoHtml('credito_mod') . '</td>';
	$sHtml .= '</tr>';
	$sHtml .= '</table>';

	$modal  = '<div id="mostrarmodal" class="modal fade" role="dialog" >
                <div class="modal-dialog modal-lg" style="width: 400px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">' . $title . '</h4>
                        </div>
                        <div class="modal-body">';
	$modal .= $sHtml;
	$modal .= '          </div>
                        <div class="modal-footer">
							<div class="btn btn-primary btn-sm" onClick="javascript:procesar( ' . $id . ', ' . $opcion . '  )" >
								<span class="glyphicon glyphicon-cog"></span>
								Procesar
							</div>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
             </div>';

	$oReturn->assign("miModal_Diario", "innerHTML", $modal);
	$oReturn->script("abre_modal();");

	return $oReturn;
}


function modificar_valor($id, $opcion, $aForm = '')
{
	global $DSN, $DSN_Ifx;
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo();
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	$aDataGrid  = $_SESSION['aDataGirdDiar'];
	$aLabelDiar = array(
		'Fila', 			 'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 'Credito Moneda Ext', 	'Detalle',		'Modificar', 		'Eliminar',
		'Centro Costo',   			'Centro Actividad'
	);


	$idempresa  = $aForm['empresa'];
	$idsucursal = $aForm['sucursal'];
	$credito    = $aForm['credito_mod'];
	$debito     = $aForm['debito_mod'];

	if ($opcion == 1) {
		// MONEDA LOCAL		
		$aDataGrid[$id][$aLabelDiar[5]] = $debito;
		$aDataGrid[$id][$aLabelDiar[6]] = $credito;
	} else {
		// MONEDA EXTRANJERA
		$aDataGrid[$id][$aLabelDiar[7]] = $debito;
		$aDataGrid[$id][$aLabelDiar[8]] = $credito;
	}

	$sHtml = '';
	$_SESSION['aDataGirdDiar'] = $aDataGrid;
	$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
	$oReturn->assign("divDiario", "innerHTML", $sHtml);

	// TOTAL DIARIO
	$oReturn->script("total_diario();");

	return $oReturn;
}
function genera_pdf_doc($idempresa, $idsucursal, $asto_cod, $ejer_cod, $prdo_cod)
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfxA = new Dbo();
	$oIfxA->DSN = $DSN_Ifx;
	$oIfxA->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();
	unset($_SESSION['pdf']);
	$oReturn = new xajaxResponse();

	$tipo     = $aForm['documento'];


	$sql = "select asto_cod_modu, asto_tipo_mov from saeasto where asto_cod_asto='$asto_cod'";
	$tipomov = consulta_string($sql, 'asto_tipo_mov', $oIfx, '');
	$codmodu = consulta_string($sql, 'asto_cod_modu', $oIfx, '');

	if ($tipomov == 'DI') {
		$sql = "select ftrn_ubi_web from saeftrn where ftrn_tip_movi='$tipomov' and ftrn_cod_modu=$codmodu and ftrn_ubi_web is not null";
		$ubi = consulta_string($sql, 'ftrn_ubi_web', $oIfx, '');
		if (empty($ubi)) {
			$ubi = 'Include/Formatos/comercial/diario.php';
		}
		include_once('../../' . $ubi . '');
		$diario = formato_diario($idempresa, $idsucursal, $asto_cod, $ejer_cod, $prdo_cod);
	} elseif ($tipomov == 'EG') {
		$sql = "select ftrn_ubi_web from saeftrn where ftrn_tip_movi='$tipomov' and ftrn_cod_modu=$codmodu and ftrn_ubi_web is not null";
		$ubi = consulta_string($sql, 'ftrn_ubi_web', $oIfx, '');
		if (empty($ubi)) {
			$ubi = 'Include/Formatos/comercial/egreso.php';
		}
		include_once('../../' . $ubi . '');
		$diario = formato_egreso($idempresa, $idsucursal, $asto_cod, $ejer_cod, $prdo_cod);
	} elseif ($tipomov == 'IN') {
		$sql = "select ftrn_ubi_web from saeftrn where ftrn_tip_movi='$tipomov' and ftrn_cod_modu=$codmodu and ftrn_ubi_web is not null";
		$ubi = consulta_string($sql, 'ftrn_ubi_web', $oIfx, '');
		if (empty($ubi)) {
			$ubi = 'Include/Formatos/comercial/ingreso.php';
		}
		include_once('../../' . $ubi . '');
		$diario = formato_ingreso($idempresa, $idsucursal, $asto_cod, $ejer_cod, $prdo_cod);
	}
	$_SESSION['pdf'] = $diario;

	$oReturn->script('generar_pdf()');
	return $oReturn;
}

//DISTRIBUCION DE CUENTAS
function form_distri($aForm = '')
{

	global $DSN, $DSN_Ifx;
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oIfx = new Dbo();
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$idempresa  = $_SESSION['U_EMPRESA'];
	$idsucursal = $aForm['sucursal'];
	$cuen_cod   = $aForm['cod_cta'];
	$cuen_nom   = $aForm['nom_cta'];
	$val_cta    = $aForm['val_cta'];

	$val_cta2 = str_replace(',', '',  $val_cta);

	$ifu = new Formulario;
	$ifu->DSN = $DSN_Ifx;

	$oReturn = new xajaxResponse();

	$sHtml .= '<table class="table table-striped table-condensed" style="width: 99%; margin-bottom: 0px;">
				<tr>
					<td colspan="4" class="bg-primary">DISTRIBUCION</td>
				</tr>';
	$sHtml .= '</table>';

	// CENTRO DE COSTOS
	$table_op .= '<table class="table table-striped table-condensed table-bordered table-hover" style="width: 90%; margin-top: 20px;" align="center">';
	$table_op .= '<tr>
						<td class="fecha_letra">SALDO:</td>
						<td class="fecha_letra" id="saldo_ccosn">0.00</td>
						<td class="fecha_letra">PORCENTAJE:</td>
						<td class="fecha_letra" id="porcen_ccosn">0.00</td>
						<td colspan="1" align="right">
							<div class="modal-footer">                    
								<button type="button" class="btn btn-info dropdown-toggle" data-dismiss="modal" onclick="agregar_dist();">Procesar</button>
								<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
							</div>
						</td>
				</tr>
				<tr>
						<td align="center" class="fecha_letra">N.-</th>
						<td align="center" class="fecha_letra">Codigo</th>
						<td align="center" class="fecha_letra">Centro Costo</th>
						<td align="center" class="fecha_letra">%</th>
						<td align="center" class="fecha_letra">Valor</th>
				</tr>';

	$sql = "select  ccosn_cod_ccosn,  ccosn_nom_ccosn, *
					from saeccosn where
					ccosn_cod_empr  = $idempresa and
					ccosn_mov_ccosn = '1' and
					ccosn_impr_sn   = 'N' 
					order by 1 ";
	$i = 1;
	unset($array_ccosn);
	if ($oIfx->Query($sql)) {
		if ($oIfx->NumFilas() > 0) {
			do {
				$ccosn_cod_ccosn  = $oIfx->f('ccosn_cod_ccosn');
				$ccosn_nom_ccosn  = $oIfx->f('ccosn_nom_ccosn');

				$ifu->AgregarCampoNumerico($ccosn_cod_ccosn . '_porc', 'Valor|left', true, '0', 80, 100);
				$ifu->AgregarComandoAlCambiarValor($ccosn_cod_ccosn . '_porc', 'cargar_datos_ccosn()');

				$ifu->AgregarCampoNumerico($ccosn_cod_ccosn . '_val', 'Valor|left', true, '0', 80, 100);
				$ifu->AgregarComandoAlCambiarValor($ccosn_cod_ccosn . '_val', 'cargar_datos_ccosnV()');

				$table_op .= '<tr>';
				$table_op .= '<td align="right">' . $i . '</td>';
				$table_op .= '<td align="right">' . $ccosn_cod_ccosn . '</td>';
				$table_op .= '<td align="left" >' . $ccosn_nom_ccosn . '</td>';
				$table_op .= '<td align="right" >' . $ifu->ObjetoHtml($ccosn_cod_ccosn . '_porc') . '</td>';
				$table_op .= '<td align="right" >' . $ifu->ObjetoHtml($ccosn_cod_ccosn . '_val') . '</td>';
				$table_op .= '</tr>';

				$i++;

				$array_ccosn[] =  array($ccosn_cod_ccosn,  $ccosn_nom_ccosn);
			} while ($oIfx->SiguienteRegistro());

			$table_op .= '<tr>';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="right"></td>';
			$table_op .= '<td align="left" ></td>';
			$table_op .= '<td align="right" id="total_ccosnp" class="letra_rojo">0.00</td>';
			$table_op .= '<td align="right" id="total_ccosn"  class="letra_rojo">0.00</td>';
			$table_op .= '</tr>';
		}
	}

	$table_op .= '</table>';

	unset($_SESSION['U_ARRAY_CCOSN']);
	$_SESSION['U_ARRAY_CCOSN'] = $array_ccosn;


	$modal  = '<div id="mostrarmodal6" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">' . $cuen_cod . ' - ' . substr($cuen_nom, 0, 50) . '</h4>
                            <h4 class="modal-title"><strong>VALOR: ' . $val_cta . '  </strong><input type="hidden" value="' . $val_cta2 . '" id="total_distribucion_m" name="total_distribucion_m"></h4>
                        </div>
                        <div class="modal-body">';
	$modal .= $sHtml;

	$sHtml .= '</div>';
	$modal .= '<div id="gridFp" style="margin-top: 20px;">' . $table_op . '</div>';
	$modal .= '          
                        <div class="modal-footer">                    
                            <button type="button" class="btn btn-info dropdown-toggle" data-dismiss="modal" onclick="agregar_dist();">Procesar</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
             </div>';

	//$oReturn->alert($sHtml);	

	$oReturn->assign("miModalDistri", "innerHTML", $modal);
	$oReturn->script("abre_modal_distri();");

	return $oReturn;
}
// DISTRIBUCION CENTRO DE COSTOS VALOR
function distribucion_ccosnV($aForm = '', $total_fact = '')
{
	//Definiciones
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oReturn = new xajaxResponse();
	$total_fact = str_replace(',', '',  $total_fact);
	$array   = $_SESSION['U_ARRAY_CCOSN'];
	// D E T A L L E     D E S C R I P C I O N
	$aDataGrid = $_SESSION['aDataGirdDist'];
	$total_distribucion_m = $aForm['total_distribucion_m'];
	if (count($array) > 0) {
		$tot_porc = 0;
		$tot_val  = 0;
		foreach ($array as $val) {
			$ccosn_cod_ccosn  = $val[0];
			$ccosn_nom_ccosn  = $val[1];
			$porc_ccosn		  = $aForm[$ccosn_cod_ccosn . '_porc'];
			$val_ccosn		  = $aForm[$ccosn_cod_ccosn . '_val'];
			if ($val_ccosn >= 0) {
				$subt = 0;
				//$subt = round(($porc_ccosn*$total_fact)/100,2);
				$porc_ccosn = round(($val_ccosn * 100) / $total_fact, 2);

				$tot_porc += $porc_ccosn;
				$tot_val  += $val_ccosn;

				if ($tot_porc > 100) {
					$tot_porc -= $porc_ccosn;
					$tot_val  -= $val_ccosn;
					/*$oReturn->alert('Sobrepaso el 100 %, Por favor revisar....');
					$oReturn->assign($ccosn_cod_ccosn.'_val', "value", 0);
					$oReturn->assign($ccosn_cod_ccosn.'_porc',"value", 0);*/
					$tot_lineap = (100 - $tot_porc);
					$tot_lineav = ($total_fact - $tot_val);
					$oReturn->alert('Sobrepaso el 100 %, Por favor revisar....');
					$oReturn->assign($ccosn_cod_ccosn . '_val', "value", $tot_lineav);
					$oReturn->assign($ccosn_cod_ccosn . '_porc', "value", $tot_lineap);
					$tot_porc += $tot_lineap;
					$tot_val  += $tot_lineav;
				} else {
					$oReturn->assign($ccosn_cod_ccosn . '_porc', "value", $porc_ccosn);
				}
			}
		}
	}


	$oReturn->assign("total_ccosnp", "innerHTML", $tot_porc);
	$oReturn->assign("total_ccosn",  "innerHTML", $tot_val);

	// SALDO
	$saldo = 0;
	$saldo = $aForm['val_cta'] - $tot_val;
	$Porcentaje = round(($saldo * 100) / $total_distribucion_m, 2);
	$oReturn->assign("saldo_ccosn",  "innerHTML", $saldo);
	$oReturn->assign("porcen_ccosn",  "innerHTML", $Porcentaje);
	//$oReturn->alert($saldo);
	return $oReturn;
}

// DISTRIBUCION CENTRO DE COSTOS PORCENTAJE
function distribucion_ccosn($aForm = '', $total_fact = '')
{
	//Definiciones
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$oReturn = new xajaxResponse();
	$total_fact = str_replace(',', '',  $total_fact);
	$array   = $_SESSION['U_ARRAY_CCOSN'];
	// D E T A L L E     D E S C R I P C I O N
	$aDataGrid = $_SESSION['aDataGirdDist'];
	$total_distribucion_m = $aForm['total_distribucion_m'];
	if (count($array) > 0) {
		$tot_porc = 0;
		$tot_val  = 0;
		foreach ($array as $val) {
			$ccosn_cod_ccosn  = $val[0];
			$ccosn_nom_ccosn  = $val[1];
			$porc_ccosn		  = $aForm[$ccosn_cod_ccosn . '_porc'];
			if ($porc_ccosn >= 0) {
				$subt = 0;
				$subt = round(($porc_ccosn * $total_fact) / 100, 2);

				$tot_porc += $porc_ccosn;
				$tot_val  += $subt;

				if ($tot_porc > 100) {
					$tot_porc -= $porc_ccosn;
					$tot_val  -= $subt;
					$tot_lineap = (100 - $tot_porc);
					$tot_lineav = ($total_fact - $tot_val);
					$oReturn->alert('Sobrepaso el 100 %, Por favor revisar....');
					$oReturn->assign($ccosn_cod_ccosn . '_val', "value", $tot_lineav);
					$oReturn->assign($ccosn_cod_ccosn . '_porc', "value", $tot_lineap);
					$tot_porc += $tot_lineap;
					$tot_val  += $tot_lineav;
				} else {
					$oReturn->assign($ccosn_cod_ccosn . '_val', "value", $subt);
				}
			}
		}
	}
	//$oReturn->alert($tot_porc);

	$oReturn->assign("total_ccosnp", "innerHTML", $tot_porc);
	$oReturn->assign("total_ccosn",  "innerHTML", $tot_val);
	// SALDO
	$saldo = 0;

	$saldo = round($aForm['val_cta'] - $tot_val, 2);
	$Porcentaje = round(($saldo * 100) / $total_distribucion_m, 2);
	$oReturn->assign("saldo_ccosn",  "innerHTML", $saldo);
	$oReturn->assign("porcen_ccosn",  "innerHTML", $Porcentaje);

	return $oReturn;
}

function procesar_distri($aForm = '')
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$fu = new Formulario;
	$fu->DSN = $DSN;

	$aDataDiar = $_SESSION['aDataGirdDiar'];
	$array_ccosn  = $_SESSION['U_ARRAY_CCOSN'];
	$act_cod    = $aForm["actividad"];


	$aLabelDiar = array(
		'Fila', 				'Cuenta', 				'Nombre', 		'Documento', 		'Cotizacion',
		'Debito Moneda Local', 		'Credito Moneda Local',
		'Debito Moneda Ext', 	'Credito Moneda Ext', 	'Detalle', 		'Modificar', 		'Eliminar',
		'Centro Costo', 			'Centro Actividad'
	);



	$oReturn = new xajaxResponse();

	$cod_cta    = $aForm["cod_cta"];
	$nom_cta    = $aForm["nom_cta"];
	$idempresa  = $_SESSION['U_EMPRESA'];
	$idsucursal = $aForm["sucursal"];
	$detalle    = $aForm["detalla_diario"];
	$documento  = $aForm["documento"];
	$mone_cod   = $aForm["moneda"];
	$tipo       = $aForm['crdb'];

	$sql        = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa ";
	$mone_base  = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '');

	if ($mone_cod == $mone_base) {
		$sql      = "select pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa ";
		$mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

		$sql = "select tcam_valc_tcam from saetcam where
                mone_cod_empr = $idempresa and
                tcam_cod_mone = $mone_extr and
                tcam_fec_tcam in (
                                    select max(tcam_fec_tcam)  from saetcam where
                                            mone_cod_empr = $idempresa and
                                            tcam_cod_mone = $mone_extr
                                )  ";

		$coti = $aForm["cotizacion_ext"]; //consulta_string_func($sql, 'tcam_val_tcam', $oIfx, 0);
	} else {
		$coti = $aForm["cotizacion"];
	}


	if (count($array_ccosn) > 0) {
		foreach ($array_ccosn as $val) {
			$ccosn_cod_ccosn  = $val[0];
			$ccosn_nom_ccosn  = $val[1];
			$porc_ccosn		  = $aForm[$ccosn_cod_ccosn . '_porc'];
			$val_ccosn  	  = $aForm[$ccosn_cod_ccosn . '_val'];
			if ($tipo == 'CR') {
				$cred = $val_ccosn;
				$deb  = 0;
			} elseif ($tipo == 'DB') {
				$cred = 0;
				$deb  = $val_ccosn;
			}
			if ($porc_ccosn > 0) {
				// DIARIO
				$contd = count($aDataDiar);
				$aDataDiar[$contd][$aLabelDiar[0]] = floatval($contd);
				$aDataDiar[$contd][$aLabelDiar[1]] = $cod_cta;
				$aDataDiar[$contd][$aLabelDiar[2]] = $nom_cta;
				$aDataDiar[$contd][$aLabelDiar[3]] = $documento;
				$aDataDiar[$contd][$aLabelDiar[4]] = $coti;

				if ($mone_cod == $mone_base) {
					// moneda local
					$cre_tmp = 0;
					$deb_tmp = 0;
					if ($coti > 0) {
						$cre_tmp = round(($cred / $coti), 2);
					}

					if ($coti > 0) {
						$deb_tmp = round(($deb / $coti), 2);
					}

					$aDataDiar[$contd][$aLabelDiar[5]] = $deb;
					$aDataDiar[$contd][$aLabelDiar[6]] = $cred;
					$aDataDiar[$contd][$aLabelDiar[7]] = $deb_tmp;
					$aDataDiar[$contd][$aLabelDiar[8]] = $cre_tmp;
				} else {
					// moneda extra			
					$aDataDiar[$contd][$aLabelDiar[5]] = $deb * $coti;
					$aDataDiar[$contd][$aLabelDiar[6]] = $cred * $coti;
					$aDataDiar[$contd][$aLabelDiar[7]] = $deb;
					$aDataDiar[$contd][$aLabelDiar[8]] = $cred;
				}

				$vacio = '-1';

				$aDataDiar[$contd][$aLabelDiar[9]]  = $detalle;
				$aDataDiar[$contd][$aLabelDiar[10]] = '';
				$aDataDiar[$contd][$aLabelDiar[11]] = '<img src="' . $_COOKIE['JIREH_IMAGENES'] . 'iconos/delete_1.png"
																		title = "Presione aqui para Eliminar"
																		style="cursor: hand !important; cursor: pointer !important;"
																		onclick="javascript:xajax_elimina_detalle_dir(' . $vacio . ', ' . $idempresa . ', ' . $idsucursal . ', ' . $contd . ', -1 );"
																		alt="Eliminar"
																		align="bottom" />';
				$aDataDiar[$contd][$aLabelDiar[12]] = $ccosn_cod_ccosn;
				$aDataDiar[$contd][$aLabelDiar[13]] = $act_cod;
			}
		}
	}

	// DIARIO
	$sHtml = '';
	$_SESSION['aDataGirdDiar'] = $aDataDiar;
	$sHtml = mostrar_grid_dia($idempresa, $idsucursal);
	$oReturn->assign("divDiario", "innerHTML", $sHtml);

	$oReturn->assign("cod_cta", "value", '');
	$oReturn->assign("nom_cta", "value", '');
	$oReturn->assign("val_cta", "value", '');
	$oReturn->assign("documento", "value", '');

	$oReturn->script("total_diario();");

	return $oReturn;
}



//----------------------------------------------------
// FUNCIONES ADJUNTOS SUEJO4667						//
//----------------------------------------------------

function modal_adjuntos($aForm = '')
{

	//Definiciones
	global $DSN_Ifx, $DSN;

	session_start();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oReturn = new xajaxResponse();

	$ifu = new Formulario;
	$ifu->DSN = $DSN_Ifx;
	//variables de session
	$idempresa = $_SESSION['U_EMPRESA'];
	$idsucursal = $_SESSION['U_SUCURSAL'];

	//variables del formulario
	$idContrato = $aForm['idContrato'];
	$latitud = $aForm['latitud'];
	$longitud = $aForm['longuitud'];
	$abonadoContrato = $aForm['abonadoContrato'];

	try {

		/*  //adjuntos
        $n = 1;
        $sql = "select adjunto 
                    from isp.int_adjuntos 
                    where id_clpv = $id_clpv and
                    id_contrato = $id_contrato and
                    id = $idComprobante";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $sHtmlAdjuntos .= '<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%; margin:0px;">
                <tr><td COLSPAN="2" class="bg-primary">IMAGENES GUARDADAS</td></tr>
                <tr class="info">
                    <td >NUMERO</td>
                    <td >IMAGEN</td>
                </tr>';
                do {
                    $adj = $oCon->f('adjunto');
                    $sHtmlAdjuntos .= '<tr>
                    <td>' . $n . '</td>
                    <td style="cursor:pointer;" onclick="descargar_adjunto(\'' . $adj . '\')"><img src="' . path(DIR_INCLUDE) . 'clases/formulario/plugins/reloj/' . substr($adj, 3) . '" width="50"/></td>
                </tr>';
                    $n++;
                } while ($oCon->SiguienteRegistro());
                $sHtmlAdjuntos .= '</table>';
            }
        }
        $oCon->free();
    */

		$today = date("Y-m-d");
		$sHtml = '<div class="modal-dialog" role="document" style="width: 100%;">
                    <div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
                            <h5 class="text-primary">Adjuntos</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
								</button>
								
							</div>
						<div class="modal-body" id="classModalBody">
                       
                     


            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="thumbnail">
                            <div class="caption">
                                <div class="row">

								<table style="width:100%">
									<tr>
										<td>
											<div class="col-md-12">
												<label for="files">Titulo</label>
												<input type="text" name="titulo_archivo" id="titulo_archivo" class="form-control">
											</div>
										</td>
										<td>
											<div class="col-md-12">
													<div class="input-group-btn">
														<label for="files">Seleecionar archivos</label>
														<input type="file" class="btn btn-primary" name="archivo" id="archivo" multiple accept="image/*,.pdf">
													</div>
											</div>
										</td>
										<td>
											<div class="col-md-12">
												<button type="button" id="btn_adj_2" class="btn btn-primary" onclick="guardarAdjuntos()" style="display:block; float:rigth">
												<i class="glyphicon glyphicon-plus"></i> Agregar</button>
											</div>  
										</td>
									</tr>
								</table>


                                    <div class="row">
                                    <div class="col-md-12">
                                        <div class="upload-msg-archivo"></div>
                                    </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-12" id="tableAdjuntos" style="max-height: 250px; overflow-y: scroll; margin:10px;">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>   
                </div>
            </div>
			';


		//$sHtml = $sHtmlAdjuntos;




		$sHtml .= '</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
						</div>
					
				</div>';
		$oReturn->script("verAdj(0)");
		$oReturn->assign("miModal", "innerHTML", $sHtml);
	} catch (Exception $e) {
		$oReturn->alert($e->getMessage());
	}

	return $oReturn;
}


function guardarAdjuntos($aForm = '')
{
	session_start();
	global $DSN, $DSN_Ifx;

	$oCon = new Dbo();
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	//variables de session
	$idempresa = $_SESSION['U_EMPRESA'];
	$idsucursal = $_SESSION['U_SUCURSAL'];
	$usuario_web = $_SESSION['U_ID'];

	//variables del formulario
	$id_contrato    = $aForm['idContrato'];
	$id_clpv        = $aForm['clpv_cod'];
	$fecha          = date("Y-m-d");
	$fechaServer    = date("Y-m-d H:i:s");
	$id_instalacion = $aForm['inst_clpv_id'];
	$archivos       = $_SESSION["archivos"];
	$titulo_adj        = $aForm['titulo_archivo'];

	$fecha          = date("Y-m-d");
	$fechaServer    = date("Y-m-d H:i:s");
	$id_instalacion = 0;
	$archivos       = $_SESSION["archivos"];

	if (isset($archivos)) {

		try {

			for ($i = 0; $i <= count($archivos); $i++) {

				if (!empty($archivos[$i])) {
					$adjunto = $archivos[$i];

					$oCon->QueryT('BEGIN;');


					$array_prod_serie = $_SESSION['PROD_TRANSFE'];


					$sql = "INSERT INTO comercial.adjuntos (id_empresa, id_sucursal, ruta, id_clpv, user_web, fecha_server, estado,titulo)
                                                                values($idempresa, $idsucursal, '$adjunto', $id_clpv, $usuario_web, '$fechaServer','PE','$titulo_adj')";
					$oCon->QueryT($sql);
					$oCon->QueryT('COMMIT;');
				}
			}

			$num_archivos = count($archivos);

			$oReturn->script("Swal.fire({
                                    type: 'success',
                                    title: 'Exito',
                                    text: 'Se han subido de manera exitosa $num_archivos archivos.'
                                })");

			unset($_SESSION["archivos"]);
			$oReturn->script('verAdj(' . $id_instalacion . ');');
		} catch (Exception $e) {
			$oCon->QueryT('ROLLBACK;');
			$oReturn->alert($e->getMessage());
		}
	} else {
		$oReturn->alert('No existen adjuntos para insertar');
	}

	return $oReturn;
}

function verAdj($aForm = '')
{

	//Definiciones
	global $DSN_Ifx, $DSN;
	session_start();

	$oCon = new Dbo;
	$oCon->DSN = $DSN;
	$oCon->Conectar();

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN;
	$oIfx->Conectar();

	$oReturn = new xajaxResponse();

	//variables de session
	$idempresa = $_SESSION['U_EMPRESA'];
	$idsucursal = $_SESSION['U_SUCURSAL'];
	$id_clpv        = $aForm['clpv_cod'];


	try {

		$sHtml .= '';
		$sHtml .= '<table class="table table-condensed table-striped table-bordered table-hover" style="width: 98%;" id="tableAdj">';
		$sHtml .= '<thead><tr>';
		$sHtml .= '<td colspan="2"><h5>ADJUNTOS <small>Reporte Informacion</small></h5></td>';
		$sHtml .= '</tr>';
		$sHtml .= '<tr>';
		$sHtml .= '<td>No.</td>';
		$sHtml .= '<td>Adjunto</td>';
		$sHtml .= '</tr></thead><tbody>';


		$array_prod_serie = $_SESSION['PROD_TRANSFE'];


		$sql = "SELECT ruta from comercial.adjuntos where id_clpv = '$id_clpv' and estado='PE' ";
		//echo $sql;exit;
		$adjuntos = '';
		if ($oCon->Query($sql)) {
			if ($oCon->NumFilas() > 0) {
				do {
					$adjunto = $oCon->f('ruta');

					$adjuntos .= $adjunto . ":";
				} while ($oCon->SiguienteRegistro());
			}
		}
		$oCon->Free();


		$data_adjuntos = explode(":", $adjuntos);

		for ($i = 0; $i < count($data_adjuntos); $i++) {
			$num = $i + 1;

			if (!empty($data_adjuntos[$i])) {
				$sHtml .= '<tr>
												<td>' . $num . '</td>
												<td><a href="#" onclick="dowloand(\'' . $data_adjuntos[$i] . '\')">' . $data_adjuntos[$i] . '</a></td>
												</tr>';
			}
		}

		$sHtml .= '</tbody></table>';

		$oReturn->assign("tableAdjuntos", "innerHTML", $sHtml);
		$oReturn->script('initTable(\'tableAdj\');');
	} catch (Exception $e) {

		$oReturn->alert($e->getMessage());
	}

	return $oReturn;
}




/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
