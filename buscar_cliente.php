<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

$oIfx = new Dbo;
$oIfx -> DSN = $DSN_Ifx;
$oIfx -> Conectar();

$oIfxA = new Dbo;
$oIfxA -> DSN = $DSN_Ifx;
$oIfxA -> Conectar();

$idempresa   = isset($_GET['empresa']) ? (int) $_GET['empresa'] : 0;
$cliente_nom = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$op          = isset($_GET['op']) ? $_GET['op'] : '';
$tipo_pago   = isset($_GET['tipo_pago']) ? $_GET['tipo_pago'] : '';
$forma_pago  = isset($_GET['forma_pago']) ? $_GET['forma_pago'] : '';

$sqlFiltroEstado = '';
$sqlEstadoEmpresa = "select emmpr_uafe_cprov from saeempr where empr_cod_empr = $idempresa";
$uafeEstado = consulta_string_func($sqlEstadoEmpresa, 'emmpr_uafe_cprov', $oIfxA, '');
if (strtolower(trim($uafeEstado)) === 't') {
    $sqlFiltroEstado = "  AND upper(trim(c.clpv_est_clpv)) = 'A'";
}
$oIfxA->Free();

$sqlListado = "select c.clpv_cod_clpv, c.clpv_nom_clpv,  c.clpv_ruc_clpv,
                c.clpv_cod_vend, c.clpv_cot_clpv, c.clpv_pre_ven, '' as direccion,
                '' as telefono, clpv_etu_clpv, clpv_cod_tpago, clpv_cod_fpagop, clpv_pro_pago,
                c.clpv_clopv_clpv
        from saeclpv c  where
        c.clpv_cod_empr       = $idempresa" .
        $sqlFiltroEstado .
        " and
   --     c.clpv_clopv_clpv     = 'PV' and
        (c.clpv_nom_clpv like upper('%$cliente_nom%') OR c.clpv_ruc_clpv like upper('%$cliente_nom%'))
        group by 1,2,3,4,5,6, 9, 10, 11, 12, 13 order by 2";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <link rel="stylesheet" type = "text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/general.css"/>
        <link href="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/Formulario/Css/Formulario.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="media/css/bootstrap.min.css"/>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>LISTA DE CLIENTE - PROVEEDORES</title>
        <link rel="stylesheet" href="media/css/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css"/>
        <script src="media/js/jquery-1.10.2.js"></script>
        <script src="media/js/jquery.dataTables.min.js"></script>
        <script src="media/js/dataTables.bootstrap.min.js"></script>
        <style type="text/css">
            <!--
            .Estilo1 {
                font-size: 12px;
                font-family: Georgia, "Times New Roman", Times, serif;
                color: #000000;
            }
            -->
        </style>

        <script>
            function datos( cod, cli, ruc, dir, tel, cel, vend ,cont, pre, fpago, tpago, fec, auto,
                            serie, fec_venc, dia, contr, ini, fin, ema, contribuyente, ab, op, clpv){
                if(op==0){
                    window.opener.document.form1.cliente.value        = cod;
                    window.opener.document.form1.cliente_nombre.value = cli;
                    window.opener.document.form1.clpv_cod.value       = cod;
                    window.opener.document.form1.clpv_nom.value       = cli;
                                        window.opener.cargar_lista_tran(clpv);
                                        window.opener.cargar_lista_subcliente();
                                        window.opener.document.form1.tipoClpv.value       = clpv;
                }else if(op==1){
                    window.opener.document.form1.clpv_cod.value = cod;
                    window.opener.document.form1.clpv_nom.value = cli;
                                        window.opener.cargar_lista_tran(clpv);
                                        window.opener.cargar_lista_subcliente();
                                        window.opener.document.form1.tipoClpv.value       = clpv;
                }
                close();
            }
        </script>
    </head>

    <body>
        <div id="contenido">
            <?
            $cont   = 1;
            $sClass = 'off';

            echo "<pre>SQL QUE EJECUTA EL MODAL:\n$sqlListado</pre>";
            ?>
            <table id="tblClientesProv" align="center" border="0" cellpadding="2" cellspacing="1" width="98%" style="border:#999999 1px solid" class="display table table-striped table-bordered">
                <thead>
                    <tr>
                        <th colspan="6" align="center" class="titulopedido">LISTA DE CLIENTES - PROVEEDORES</th>
                    </tr>
                    <tr>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">ID</th>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">TIPO</th>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">CODIGO ITEM</th>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">PROVEEDOR</th>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">IDENTIFICACION</th>
                        <th align="left" bgcolor="#EBF0FA" class="titulopedido">CONTRIBUYENTE ESPECIAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    if ($oIfx->Query($sqlListado)) {
                        if( $oIfx->NumFilas() > 0 ) {
                            do {
                                $codigo      = ($oIfx->f('clpv_cod_clpv'));
                                $nom_cliente = htmlentities($oIfx->f('clpv_nom_clpv'));
                                $ruc         = ($oIfx->f('clpv_ruc_clpv'));
                                $dire        = htmlentities($oIfx->f('direccion'));
                                $telefono    = $oIfx->f('telefono');
                                $celular     = $oIfx->f('celular');
                                $vendedor    = $oIfx->f('clpv_cod_vend');
                                $contacto    = $oIfx->f('clpv_cot_clpv');
                                $precio      = round($oIfx->f('clpv_pre_ven'),0);
                                $fpago       = $oIfx->f('clpv_cod_fpagop');
                                $tpago       = $oIfx->f('clpv_cod_tpago');
                                $prove_dia   = $oIfx->f('clpv_pro_pago');
                                $contribuyente_especial = $oIfx->f('clpv_etu_clpv');
                                $clpv_etu_clpv = ($contribuyente_especial == 1 || strtoupper(trim($contribuyente_especial)) === 'S') ? 'S' : 'N';
                                $cl_pv       = $oIfx->f('clpv_clopv_clpv');

                                if(empty($prove_dia)) {
                                    $prove_dia = 0;
                                }

                                // correo
                                $sqlCorreo = "select min( emai_ema_emai ) as correo from saeemai where
                                                emai_cod_empr = $idempresa and
                                                emai_cod_clpv = $codigo ";
                                $correo = acento_func(consulta_string_func($sqlCorreo, 'correo', $oIfxA, ''));

                                // FECHA DE VENCIMIENTO
                                $fecha_venc = (sumar_dias_func( date("Y-m-d"), $prove_dia)); //  Y/m/d

                                // AUTORIZACION PROVE
                                $sqlAutorizacion = "select  max(coa_fec_vali) as coa_fec_vali, coa_aut_usua, coa_seri_docu, coa_fact_ini, coa_fact_fin
                                                from saecoa where
                                                clpv_cod_empr = $idempresa and
                                                clpv_cod_clpv = $codigo group by coa_fec_vali,2,3,4,5 ";
                                $fec_cadu_prove = ''; $auto_prove = ''; $serie_prove = '';  $ini_prove = ''; $fin_prove='';
                                if($oIfxA->Query($sqlAutorizacion)) {
                                    if($oIfxA->NumFilas()>0) {
                                        $fec_cadu_prove = fecha_mysql_func2($oIfxA->f('coa_fec_vali'));
                                        $auto_prove = $oIfxA->f('coa_aut_usua');
                                        $serie_prove = $oIfxA->f('coa_seri_docu');
                                        $ini_prove = $oIfxA->f('coa_fact_ini');
                                        $fin_prove = $oIfxA->f('coa_fact_fin');
                                    }
                                }
                                $oIfxA->Free();

                                if($cl_pv=='CL'){
                                    $clase = 'letra_rojo';
                                }else{
                                    $clase = 'visited';
                                }

                                if ($sClass=='off') $sClass='on'; else $sClass='off';
                    ?>
                                <tr height="20" class="<?=$sClass?>" onMouseOver="javascript:this.className='link';" onMouseOut="javascript:this.className='<?=$sClass?>';">
                                    <td align="rigth" class="<?=$clase?>"><?=$cont?></td>
                                    <td width="100" class="<?=$clase?>">
                                        <a href="#" onclick="datos('<?=$codigo?>',    '<?=$nom_cliente?>',  '<?=$ruc?>',
                                                                   '<?=$dire?>',      '<?=$telefono?>',     '<?=$celular?>',
                                                                   '<?=$vendedor?>',  '<?=$contacto?>',     '<?=$precio?>',
                                                                   '<?=$fpago?>',     '<?=$tpago?>',        '<?=$fec_cadu_prove?>',
                                                                   '<?=$auto_prove?>','<?=$serie_prove?>',  '<?=$fecha_venc?>',
                                                                   '<?=$prove_dia?>', '<?=$clpv_etu_clpv?>','<?=$ini_prove?>',
                                                                   '<?=$fin_prove?>', '<?=$correo?>',       '<?=$tipo_pago?>',
                                                                   '<?=$forma_pago?>','<?=$op?>',           '<?=$cl_pv?>' )">
                                            <span class="<?=$clase?>"><?=$cl_pv?></span>
                                        </a>
                                    </td>
                                    <td width="100" align="right" class="<?=$clase?>">
                                        <a href="#" onclick="datos('<?=$codigo?>',    '<?=$nom_cliente?>',  '<?=$ruc?>',
                                                                   '<?=$dire?>',      '<?=$telefono?>',     '<?=$celular?>',
                                                                   '<?=$vendedor?>',  '<?=$contacto?>',     '<?=$precio?>',
                                                                   '<?=$fpago?>',     '<?=$tpago?>',        '<?=$fec_cadu_prove?>',
                                                                   '<?=$auto_prove?>','<?=$serie_prove?>',  '<?=$fecha_venc?>',
                                                                   '<?=$prove_dia?>', '<?=$clpv_etu_clpv?>','<?=$ini_prove?>',
                                                                   '<?=$fin_prove?>', '<?=$correo?>',       '<?=$tipo_pago?>',
                                                                   '<?=$forma_pago?>','<?=$op?>',           '<?=$cl_pv?>' )">
                                            <span class="<?=$clase?>"> <?=$codigo?></span>
                                        </a>
                                    </td>
                                    <td class="<?=$clase?>">
                                        <a href="#" onclick="datos('<?=$codigo?>',    '<?=$nom_cliente?>',  '<?=$ruc?>',
                                                                   '<?=$dire?>',      '<?=$telefono?>',     '<?=$celular?>',
                                                                   '<?=$vendedor?>',  '<?=$contacto?>',     '<?=$precio?>',
                                                                   '<?=$fpago?>',     '<?=$tpago?>',        '<?=$fec_cadu_prove?>',
                                                                   '<?=$auto_prove?>','<?=$serie_prove?>',  '<?=$fecha_venc?>',
                                                                   '<?=$prove_dia?>', '<?=$clpv_etu_clpv?>','<?=$ini_prove?>',
                                                                   '<?=$fin_prove?>', '<?=$correo?>',       '<?=$tipo_pago?>',
                                                                   '<?=$forma_pago?>', '<?=$op?>' ,         '<?=$cl_pv?>' )">
                                            <span class="<?=$clase?>"> <?=$nom_cliente?> </span>
                                        </a>
                                    </td>
                                    <td class="<?=$clase?>">
                                        <a href="#" onclick="datos('<?=$codigo?>',    '<?=$nom_cliente?>',  '<?=$ruc?>',
                                                                   '<?=$dire?>',      '<?=$telefono?>',     '<?=$celular?>',
                                                                   '<?=$vendedor?>',  '<?=$contacto?>',     '<?=$precio?>',
                                                                   '<?=$fpago?>',     '<?=$tpago?>',        '<?=$fec_cadu_prove?>',
                                                                   '<?=$auto_prove?>','<?=$serie_prove?>',  '<?=$fecha_venc?>',
                                                                   '<?=$prove_dia?>', '<?=$clpv_etu_clpv?>','<?=$ini_prove?>',
                                                                   '<?=$fin_prove?>', '<?=$correo?>',       '<?=$tipo_pago?>',
                                                                   '<?=$forma_pago?>', '<?=$op?>' ,         '<?=$cl_pv?>' )">
                                            <span class="<?=$clase?>"> <?=$ruc?></span>
                                        </a>
                                    </td>
                                    <td class="<?=$clase?>">
                                        <a href="#" onclick="datos('<?=$codigo?>',    '<?=$nom_cliente?>',  '<?=$ruc?>',
                                                                   '<?=$dire?>',      '<?=$telefono?>',     '<?=$celular?>',
                                                                   '<?=$vendedor?>',  '<?=$contacto?>',     '<?=$precio?>',
                                                                   '<?=$fpago?>',     '<?=$tpago?>',        '<?=$fec_cadu_prove?>',
                                                                   '<?=$auto_prove?>','<?=$serie_prove?>',  '<?=$fecha_venc?>',
                                                                   '<?=$prove_dia?>', '<?=$clpv_etu_clpv?>','<?=$ini_prove?>',
                                                                   '<?=$fin_prove?>', '<?=$correo?>',       '<?=$tipo_pago?>',
                                                                   '<?=$forma_pago?>', '<?=$op?>',          '<?=$cl_pv?>' )">
                                            <span class="<?=$clase?>"><?=$clpv_etu_clpv?></span>
                                        </a>
                                    </td>
                                </tr>
                    <?
                                $cont++;
                            }while($oIfx->SiguienteRegistro());
                        }else {
                    ?>
                        <tr>
                            <td colspan="6" class="fecha_letra">Sin Datos....</td>
                        </tr>
                    <?
                        }
                    }
                    $oIfx->Free();
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6">Se mostraron <?=($cont-1)?> Registros</td>
                    </tr>
                </tfoot>
            </table>
            <?
            function fecha_mysql_func2($fecha) {
                if (empty($fecha)) {
                    return '';
                }

                $fecha_array = explode('/', $fecha);
                if (count($fecha_array) < 3) {
                    return '';
                }

                $m = isset($fecha_array[0]) ? $fecha_array[0] : '';
                $d = isset($fecha_array[1]) ? $fecha_array[1] : '';
                $y = isset($fecha_array[2]) ? $fecha_array[2] : '';

                if ($m === '' || $d === '' || $y === '') {
                    return '';
                }

                return ( $y.'/'.$m.'/'.$d );
            }
            ?>
        </div>
        <script type="text/javascript">
            (function($) {
                function inicializarTabla() {
                    var $tabla = $('#tblClientesProv');
                    if (!$tabla.length || $.fn.DataTable.isDataTable($tabla[0])) {
                        return;
                    }

                    $tabla.DataTable({
                        dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                             "<'row'<'col-sm-12'tr>>" +
                             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        deferRender: true,
                        language: {
                            decimal: '',
                            emptyTable: 'No hay datos disponibles en la tabla',
                            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                            infoFiltered: '(filtrado de _MAX_ registros totales)',
                            infoPostFix: '',
                            thousands: ',',
                            lengthMenu: 'Mostrar _MENU_ registros',
                            loadingRecords: 'Cargando...',
                            processing: 'Procesando...',
                            search: 'Buscar:',
                            zeroRecords: 'No se encontraron registros coincidentes',
                            paginate: {
                                first: 'Primero',
                                last: 'Último',
                                next: 'Siguiente',
                                previous: 'Anterior'
                            },
                            aria: {
                                sortAscending: ': activar para ordenar la columna de manera ascendente',
                                sortDescending: ': activar para ordenar la columna de manera descendente'
                            }
                        }
                    });
                }

                inicializarTabla();
            })(jQuery);
        </script>
    </body>
</html>
