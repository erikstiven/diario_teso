<?

/********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /********************************************************************/ ?>


    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/treeview/css/bootstrap-treeview.css" media="screen">
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.bootstrap.min.css">
    <!--JavaScript-->
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/treeview/js/bootstrap-treeview.js"></script>
    <script type="text/javascript" language="javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/Webjs.js"></script>
    <script type="text/javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.bootstrap.min.js"></script>


    <script src="js/jquery.min.js" type="text/javascript"></script>

    <!--Javascript-->
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>
    <script src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>




    <?php
    $id_parcial = 0;

    unset($_SESSION['id_partial_2']);
    unset($_SESSION['id_partial_3']);
    if (isset($_GET['codigo_solicitud'])) {
        $id_parcial = $_GET['codigo_solicitud'];
        $tipo_solicitud = $_GET['tipo_solicitud'];
        $_SESSION['id_partial_2'] = $id_parcial;
        $_SESSION['id_partial_3'] = $tipo_solicitud;
    }

    unset($_SESSION['GESTION_IMPORTACION']);
    if (isset($_GET['importacion_modulos'])) {
        $importacion_modulos = $_GET['importacion_modulos'];
        $_SESSION['GESTION_IMPORTACION'] = $importacion_modulos;
    }
    if (isset($_GET['idModulo'])) {
        $idModal = $_GET['idModulo'];
        $idClpv = $_GET['idClpv'];
        $idCta = $_GET['idCta'];
        $totalC = $_GET['total'];
        $comision = $_GET['comision'];
        $idFact = $_GET['idFact'];
        $idCheq = $_GET['idCheq'];
        $idCact = $_GET['idCact'];
        $idArray = $_GET['idArray'];

        // var_dump($idArray);exit;
    } else {
        $idModal = 0;
        $idClpv = 0;
        $idCta = 0;
        $totalC = 0;
        $comision = 0;
        $idFact = 0;
        $idCheq = 0;
        $idCact = 0;
        $idArray = 0;
    }

    if (isset($_GET['ArrayBal'])) {
        $codModu=$_GET['codModu'];
        $arrayBal=$_GET['ArrayBal'];

    }
    else{
        $codModu=0;
    }

    ?>


    <script>
        //Variable modulo cheques protestados
        var idModal = '<?= $idModal ?>';

        var id_parcial_ = '<?= $id_parcial ?>';

        var importacion_modulos_ = '<?= $importacion_modulos ?>';

        if (id_parcial_ > 0) {
            padre = $(window.parent.document);
            idModulo = 197;
        } else {
            //id de modulo
            padre = $(window.parent.document);
            idModulo = $(padre).find("#idModuloMenu").val();
        }

        if (importacion_modulos_ > 0) {
            // idModulo = 141 es el diario de CXP
            idModulo = 141;
        }
        if (idModal > 0) {
            idModulo = idModal;
            idClpv = <?= $idClpv ?>;
            idCta = <?= $idCta ?>;
            totalC = <?= $totalC ?>;
            idFact = <?= $idFact ?>;

        } else {
            idClpv = '';
            idCta = '';
            totalC = '';
            idFact = '';

        }

        if (<?= $codModu ?> > 0) {
            idModulo=<?= $codModu ?>;
        }

        function genera_formulario() {
            xajax_genera_formulario('nuevo', xajax.getFormValues("form1"), idModulo, idClpv, idCta, <?= $codModu ?>)
        }



        function cargar_sucu() {
            xajax_genera_formulario('sucursal', xajax.getFormValues("form1"), idModulo, idClpv, idCta, <?= $codModu ?>);
        }

        function cargar_tran() {
            xajax_genera_formulario('tran', xajax.getFormValues("form1"), idModulo, idClpv, idCta, <?= $codModu ?>);
        }

        function archivosAdjuntos() {
            var id = document.getElementById("cliente").value;
            if (id != '') {
                document.getElementById("miModal").innerHTML = '';
                $("#miModal").modal("show");
                xajax_archivosAdjuntos(xajax.getFormValues("form1"));
            } else {
                var mensaje = "Seleccione Proveedor para continuar";
                var tipo = "info";
                alerts(mensaje, tipo);

            }
        }

        function agregarArchivo() {
            xajax_agrega_modifica_gridAdj(0, xajax.getFormValues("form1"), '', '');
        }

        //alertas
        function alerts(mensaje, tipo) {
            if (tipo == 'success') {
                Swal.fire({
                    type: tipo,
                    title: mensaje,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 2000,
                    width: '600',
                })
            } else {
                Swal.fire({
                    type: tipo,
                    title: mensaje,

                    showCancelButton: false,
                    showConfirmButton: true,
                    width: '600',

                })
            }

        }

        //DATOS CLIENTE CHEQUES PROTESTADOS
        function datos_clpv(cod, cli, tipo, cta, ncta, ctcos, cact) {

            //DATOS CLIENTE
            document.form1.cliente.value = cod;
            document.form1.cliente_nombre.value = cli;
            document.form1.clpv_cod.value = cod;
            document.form1.clpv_nom.value = cli;
            cargar_lista_tran(tipo);
            cargar_lista_subcliente();
            document.form1.tipoClpv.value = tipo;

            //DATOS DIRECTORIO
            document.getElementById('detalle').value = 'CHEQUE PROTESTADO';
            //DATOS CUENTA BANCARIA
            document.form1.cod_cta.value = cta;
            document.form1.nom_cta.value = ncta;

            // CENTRO DE COSTO  - CENTRO ACTIVIDAD
            centro_costo_cuen(ctcos);
            centro_actividad(cact);

            //DATOS DIARIO
            cargar_tran_che();
        }
        //DIRECTORIO CHEQUE PROTESTADOS
        function anadir_dir_cheq() {
            xajax_agrega_modifica_grid_dir_cheq(0, xajax.getFormValues("form1"), <?= $totalC ?>, <?= $comision ?>, <?= $idCact ?>, <?= $idCheq ?>);
        }
        //CARGA LA TRANSACCION CHEQUES PROTESTADOS
        function cargar_tran_che() {
            xajax_agrega_dir_che();
        }

        function anadir_dasi_che() {
            if (idClpv != 0) {
                var a = setInterval(function() {
                    xajax_agrega_diario_che(<?= $idCheq ?>);
                    clearInterval(a);
                }, 2000);
            }
        }

        function autocompletar(empresa, event, op) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var empl = document.form1.clpv_empl.checked;
                var cliente_nom = '';
                if (empl == false) {
                    if (op == 0) {
                        cliente_nom = document.getElementById('cliente_nombre').value;
                    } else {
                        cliente_nom = document.getElementById('clpv_nom').value;
                    }
                    var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                    var pagina = '../diario_teso/buscar_cliente.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom + '&empresa=' + empresa + '&op=' + op;
                    window.open(pagina, "", opciones);
                } else {
                    cliente_nom = document.getElementById('cliente_nombre').value;
                    var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                    var pagina = '../comprob_egreso/buscar_empl.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom + '&empresa=' + empresa + '&op=' + op;
                    window.open(pagina, "", opciones);

                } // fin if
            }
        }

        function guardar() {
            if (ProcesarFormulario() == true) {
                var asto_cod = document.getElementById('asto_cod').value;
                var compr_cod = document.getElementById('compr_cod').value;
                if (asto_cod == '' && compr_cod == '') {
                    document.getElementById("guardar").style.display = "none";
                    xajax_guardar(xajax.getFormValues("form1"), <?= $idArray ?>, <?= $idFact ?>,<?= $codModu ?>);
                } else {
                    alert('Diario ya registrado');
                }

            }
        }

        function habilitar_boton() {
            document.getElementById("guardar").style.display = "inherit";
        }

        function consultar() {
            xajax_consultar(xajax.getFormValues("form1"));
        }

        function cerrar_ventana() {
            CloseAjaxWin();
        }



        function cargar_modifi(id_p, empresa) {
            AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../config_proc/modificar.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&id_p=' + id_p + '&empresa=' + empresa, 'DetalleShow', 'iframe', 'Modificar Procesos', '1400', '300', '10', '10', '1', '1');
        }


        function anadir_mp() {
            xajax_agrega_modifica_grid_mp(0, 0, xajax.getFormValues("form1"));
        }

        function cargar_grid_mp() {
            xajax_cargar_grid_mp(0, xajax.getFormValues("form1"));
        }

        function cargar_grid_in() {
            xajax_cargar_grid_in(0, xajax.getFormValues("form1"));
        }

        function anadir_in() {
            xajax_agrega_modifica_grid_in(0, 0, xajax.getFormValues("form1"));
        }


        function facturas(empresa, event) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var factura = document.getElementById('factura').value;
                if (factura.length == 0) {
                    factura = '';
                }
                var sucu = document.getElementById('sucursal').value;
                var clpv = document.getElementById('clpv_cod').value;
                var tran = document.getElementById('tran').value;
                var det = document.getElementById('det_dir').value;
                var tipo = document.getElementById('tipoClpv').value;
                var ccli = document.getElementById('ccli').value;
                var coti = document.getElementById('cotizacion').value;
                var mone = document.getElementById('moneda').value;
                var coti_ext = document.getElementById('cotizacion_ext').value;

                var array = [factura, sucu, clpv, empresa, tran, det, tipo, ccli, coti, mone, coti_ext];
                AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../diario_teso/buscar_factura.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&array=' + array, 'DetalleShow', 'iframe', 'FACTURAS', '800', '300', '10', '10', '1', '1');
            }
        }

        function cod_retencion(empresa, event) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var codret = '';

                codret = document.getElementById('cod_ret').value;
                clpv_cod = document.getElementById('clpv_cod').value;

                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../diario_teso/buscar_codret.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&codret=' + codret + '&empresa=' + empresa + '&clpv_cod=' + clpv_cod;
                window.open(pagina, "", opciones);


            }

        }

        /*

        function fact_retencion(empresa, event) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var factura = document.getElementById('fact_ret').value;
                if (factura.length == 0) {
                    factura = '';
                }
                var sucu = document.getElementById('sucursal').value;
                var clpv = document.getElementById('clpv_cod').value;
                var cod_ret = document.getElementById('cod_ret').value;
                var array = [factura, sucu, clpv, empresa, cod_ret];
                AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../diario_cxc/buscar_fact_ret.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&array=' + array, 'DetalleShow', 'iframe', 'FACTURAS', '800', '300', '10', '10', '1', '1');
            }
        }
            
        */

        function fact_retencion(empresa, event) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var factura = document.getElementById('fact_ret').value;
                if (factura.length == 0) {
                    factura = '';
                }
                var sucu = document.getElementById('sucursal').value;
                var clpv = document.getElementById('clpv_cod').value;
                if (clpv == '') {
                    alert('Seleccione un beneficiario para buscar facturas')
                } else {
                    var cod_ret = document.getElementById('cod_ret').value;
                    var array = [factura, sucu, clpv, empresa, cod_ret];
                    AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../rete_diario/buscar_fact_ret.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&array=' + array, 'DetalleShow', 'iframe', 'FACTURAS', '800', '300', '10', '10', '1', '1');
                }
            }
        }


        

        function replicar_valor() {

            var numero_factura = document.getElementById('fact_ret').value;
            var detalle_ret = document.getElementById('ret_det').value;
            var ret_base = document.getElementById('valor_retenido').value;
            var transaccion = document.getElementById('tran_ret').value;




            document.form1.factura.value = numero_factura;
            document.form1.det_dir.value = detalle_ret;
            document.form1.fact_valor.value = ret_base;
            document.form1.tran.value = transaccion;




            xajax_replicar_valor(xajax.getFormValues("form1"));


          
        }


        function anadir_ret() {
            xajax_agrega_modifica_grid_ret(0, xajax.getFormValues("form1"));
            replicar_valor();
            anadir_dir();
        }

        function auto_dasi(empresa, event, op) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                if (op == 0) {
                    var nom = document.getElementById('nom_cta').value;
                } else {
                    var cod = document.getElementById('cod_cta').value;
                }
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../diario_teso/buscar_cuentas.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + nom + '&empresa=' + empresa + '&op=' + op + '&codigo=' + cod;
                window.open(pagina, "", opciones);
            }
        }

        function anadir_dasi() {
            var valor = document.getElementById('val_cta').value;
            if (valor.length > 0) {
                xajax_agrega_modifica_grid_dia(0, xajax.getFormValues("form1"));
            } else {
                alert('!!! Por favor Ingrese el Valor...');
            }
        }

        function anadir_dasi_asi_inic(){
            xajax_agrega_modifica_grid_dia_asi_inic(0, xajax.getFormValues("form1"),<?=$arrayBal?>);
        }

        function numero_ret(op) {
            xajax_numero_ret(xajax.getFormValues("form1"), op);
        }

        function total_diario() {
            xajax_total_diario(xajax.getFormValues("form1"));
        }

        function cargar_detalle() {
            var msn = document.getElementById('detalle').value;
            document.getElementById('det_dir').value = msn.toUpperCase();
            document.getElementById('ret_det').value = msn.toUpperCase();
            document.getElementById('detalla_diario').value = msn.toUpperCase();
            document.getElementById('detalle').value = msn.toUpperCase();
        }


        /* function vista_previa() {
            var sucursal  = document.getElementById("sucursal").value;
            var cod_prove = document.getElementById("cliente").value;
            var asto_cod  = document.getElementById("asto_cod").value;
            var ejer_cod  = document.getElementById("ejer_cod").value;
            var prdo_cod  = document.getElementById("prdo_cod").value;
			var tipo = document.getElementById("tipo_doc").value;
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
            var pagina = '../diario_teso/vista_previa.php?sesionId=<?= session_id() ?>&sucursal='+  sucursal+'&cod_prove='+cod_prove+'&asto='+asto_cod+'&ejer='+ejer_cod+'&mes='+prdo_cod+'&tipo='+tipo;
            window.open(pagina, "", opciones);
        }*/
        function vista_previa() {
            var empresa = document.getElementById("empresa").value;
            var sucursal = document.getElementById("sucursal").value;
            var cod_prove = document.getElementById("cliente").value;
            var asto_cod = document.getElementById("asto_cod").value;
            var ejer_cod = document.getElementById("ejer_cod").value;
            var prdo_cod = document.getElementById("prdo_cod").value;
            var tipo = document.getElementById("tipo_doc").value;
            /* var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
             var pagina = '../contabilidad_comprobante/vista_previa.php?sesionId=<?= session_id() ?>&sucursal='+  sucursal+'&cod_prove='+cod_prove+'&asto='+asto_cod+'&ejer='+ejer_cod+'&mes='+prdo_cod+'&tipo='+tipo;
             window.open(pagina, "", opciones);*/
            xajax_genera_pdf_doc(empresa, sucursal, asto_cod, ejer_cod, prdo_cod);
        }

        function transaccionClpv(op) {
            xajax_cargar_lista_tran(xajax.getFormValues("form1"), op);
        }

        function eliminar_lista_tran() {
            var sel = document.getElementById("tran");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_tran(x, i, elemento) {
            var lista = document.form1.tran;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function cargar_lista_subcliente() {
            xajax_cargar_lista_subcliente(xajax.getFormValues("form1"));
        }

        function eliminar_lista_subcliente() {
            var sel = document.getElementById("ccli");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_subcliente(x, i, elemento) {
            var lista = document.form1.ccli;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function calculaValorRetenido() {
            xajax_calculaValorRetenido(xajax.getFormValues("form1"));
        }

        function valorSaldo() {
            xajax_valorSaldo(xajax.getFormValues("form1"));
        }


        function anadir_dir() {
            xajax_agrega_modifica_grid_dir_ori(0, xajax.getFormValues("form1"));
        }


        function cargar() {
            var detalle = document.getElementById("detalle_fact_vent").value;
            if (detalle != '') {

                var empresa = document.getElementById('empresa').value;

                var coti = document.getElementById('cotizacion').value;
                var mone = document.getElementById('moneda').value;
                var coti_ext = document.getElementById('cotizacion_ext').value;
                xajax_agrega_modifica_grid_dir(0, xajax.getFormValues("form1"), 0, empresa, coti, mone, coti_ext);
                $("#mostrarmodal").modal("hide");
                setTimeout(
                    function() {
                        cheque()
                    }, 2000
                );
            } else {
                alert('Debe de ingresar un detalle');
            }

        }


        function cargar_coti() {
            xajax_cargar_coti(xajax.getFormValues("form1"));
        }


        function prestamo() {
            var empr = document.getElementById('empresa').value;
            var sucu = document.getElementById('sucursal').value;
            var clpv = document.getElementById('cliente_nombre').value;
            var detalle = document.getElementById('detalle').value;
            var clpv = document.getElementById('cliente').value;
            var coti = document.getElementById('cotizacion').value;
            var mone = document.getElementById('moneda').value;
            var coti_ext = document.getElementById('cotizacion_ext').value;

            if (clpv.length == 0 || detalle.length == 0) {
                alert('Por favor Ingrese Beneficiario o Detalle...');
            } else {
                var array = [empr, sucu, clpv, detalle, mone, coti, coti_ext];

                AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../diario_teso/prestamo_empleado.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&&array=' + array, 'DetalleShow', 'iframe', 'Prestamo', '900', '300', '10', '10', '1', '1');
            }
        }



        function centro_costo_cuen(id) {
            if (id == 'S') {
                document.getElementById('ccosn').value = '';
                document.getElementById('ccosn').disabled = false;
                document.getElementById("btn_dis_ccos").style.display = '';
            } else if (id == 'N') {
                document.getElementById('ccosn').value = '';
                document.getElementById('ccosn').disabled = true;
                document.getElementById("btn_dis_ccos").style.display = 'none';
            }
        }


        function centro_actividad(id) {
            if (id == 'S') {
                document.getElementById('actividad').value = '';
                document.getElementById('actividad').disabled = false;
            } else if (id == 'N') {
                document.getElementById('actividad').value = '';
                document.getElementById('actividad').disabled = true;
            }
        }




        function cargar_lista_tran(op) {
            xajax_cargar_lista_tran(xajax.getFormValues("form1"), op);
        }


        function eliminar_lista_tran() {
            var sel = document.getElementById("tran");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_tran(x, i, elemento) {
            var lista = document.form1.tran;
            var option = new Option(elemento, i);
            lista.options[x] = option;

            var lista2 = document.form1.tran_ret;
            var option = new Option(elemento, i);
            lista2.options[x] = option;
        }



        function cargar_lista_subcliente() {
            xajax_cargar_lista_subcliente(xajax.getFormValues("form1"));
        }


        function eliminar_lista_subcliente() {
            var sel = document.getElementById("ccli");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_subcliente(x, i, elemento) {
            var lista = document.form1.ccli;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }


        function controlPeriodoIfx() {
            xajax_controlPeriodoIfx(xajax.getFormValues("form1"));
        }


        function calculaValorRetenido() {
            xajax_calculaValorRetenido(xajax.getFormValues("form1"));
        }


        function mascara(o, f) {
            v_obj = o;
            v_fun = f;
            setTimeout("execmascara()", 1);
        }

        function execmascara() {
            v_obj.value = v_fun(v_obj.value);
        }

        function cpf(v) {
            v = v.replace(/([^0-9\.]+)/g, '');
            v = v.replace(/^[\.]/, '');
            v = v.replace(/[\.][\.]/g, '');
            v = v.replace(/\.(\d)(\d)(\d)/g, '.$1$2');
            v = v.replace(/\.(\d{1,2})\./g, '.$1');
            v = v.toString().split('').reverse().join('').replace(/(\d{3})/g, '$1,');
            v = v.split('').reverse().join('').replace(/^[\,]/, '');
            return v;
        }


        function enter_dir(event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                anadir_dir();
            }
        }

        function enter_dasi(event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                anadir_dasi();
            }
        }


        function modificar_valor(id, empresa, sucursal) {
            xajax_form_modificar_valor(id, empresa, sucursal, xajax.getFormValues("form1"));
        }

        function abre_modal() {
            $("#mostrarmodal").modal("show");
        }

        function procesar(id, opcion) {
            xajax_modificar_valor(id, opcion, xajax.getFormValues("form1"));
            $("#mostrarmodal").modal("hide");
        }


        function muestra_botones() {
            document.getElementById("botones").style.display = '';

        }

        function controlPeriodoIfx() {
            xajax_controlPeriodoIfx(xajax.getFormValues("form1"));
        }

        function generar_pdf() {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=370, top=255, left=130";
            var pagina = '../../Include/documento_pdf.php?sesionId=<?= session_id() ?>';
            window.open(pagina, "", opciones);
        }
        ///// DISTRIBUCION POR CENTROS DE COSTOS
        function anadir_dasi_distri() {
            var cuen = document.getElementById('cod_cta').value;
            var crdb = document.getElementById('crdb').value;
            var val_cta = document.getElementById('val_cta').value;
            if (cuen.length > 0) {
                if (crdb.length > 0) {
                    if (val_cta.length > 0) {
                        xajax_form_distri(xajax.getFormValues("form1"));
                    } else {
                        alert('!!!! Por favor Ingrese un valor....');
                    }
                } else {
                    alert('!!!! Por favor Seleccione un tipo....');
                }
            } else {
                alert('!!!! Por favor Ingrese Cuenca Contable....');
            }

        }

        function abre_modal_distri() {
            //alert('asa');
            $("#mostrarmodal6").modal("show");
        }

        function cargar_datos_ccosnV() {
            var total = document.getElementById('val_cta').value;
            xajax_distribucion_ccosnV(xajax.getFormValues("form1"), total);
        }


        function cargar_datos_ccosn() {
            var total = document.getElementById('val_cta').value;
            xajax_distribucion_ccosn(xajax.getFormValues("form1"), total);
        }

        function agregar_dist() {
            xajax_procesar_distri(xajax.getFormValues("form1"));
        }

        function actualizar_estado_gestion_importacion(id_movimiento_actualzar, idempresa, sucursal, idejer, idprdo, cliente, secu_minv, tran, descripcion_modulo, estado) {
            window.opener.actualizar_estado_gestion_importacion(id_movimiento_actualzar, idempresa, sucursal, idejer, idprdo, cliente, secu_minv, tran, descripcion_modulo, estado);
            window.close();
        }


        //-----------------------------------------------------------
        // 			INICIO ADJUNTOS  SUEJO4967					   //
        //-----------------------------------------------------------


        function abre_modal_adjuntos() {

            var clpv = document.getElementById('clpv_cod').value;

            if (clpv > 0) {


                $("#miModal").html("");
                $("#miModal").modal("show");
                xajax_modal_adjuntos(xajax.getFormValues("form1"));



            } else {

                Swal.fire({
                    position: 'center',
                    type: 'warning',
                    title: '<h5>Porfavor selecione un <b>Beneficiario</b> para continuar</h5>',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar',
                    timer: 2000
                })

            }


        }

        function setup_adj() {
            Webcam.reset();
            Webcam.attach('#my_camera_adj');
            $("#my_camera_adj").css("display", "block");
            $("#my_camera_adj_btn").css("display", "block");
            $("#results_adj").css("display", "none");
            $("#archivo_up").css("display", "none");
            $("#btn_adj_1").css("display", "none");
            $("#btn_adj_2").css("display", "none");
        }

        function muestra_adj() {
            $("#my_camera_adj").css("display", "none");
            $("#my_camera_adj_btn").css("display", "none");
            $("#results_adj").css("display", "none");
            $("#archivo_up").css("display", "block");
            $("#btn_adj_1").css("display", "none");
            $("#btn_adj_2").css("display", "block");
            Webcam.reset()
        }



        function guardarAdjuntosImg() {
            var base64image = document.getElementById("imageprevadj").src;

            Webcam.upload(base64image, 'upload_adj.php', function(code, text) {
                xajax_guardarAdjuntosImg(xajax.getFormValues("form1"), text);
            });
        }

        function guardarAdjuntos() {
            var id = "archivo";

            $(".upload-msg-archivo").text('Cargando...');
            var inputFileImage = document.getElementById(id);
            var files = inputFileImage.files;
            var data = new FormData();
            for (i = 0; i < files.length; i++) {
                var file = files.item(i)
                data.append(i, file);
            }
            $.ajax({
                url: "upload_archivo.php?id=" + id, // Url to which the request is send
                type: "POST", // Type of request to be send, called as method
                data: data, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                contentType: false, // The content type used when sending data to the server.
                cache: false, // To unable request pages to be cached
                processData: false, // To send DOMDocument or non processed data file it is set to false
                success: function(data) // A function to be called if request succeeds
                {
                    $(".upload-msg-archivo").html(data);
                    window.setTimeout(function() {
                        $(".alert-dismissible").fadeTo(500, 0).slideUp(500, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            });
        }

        function guardarAjuntospg() {
            xajax_guardarAdjuntos(xajax.getFormValues("form1"));
        }

        function verAdj(id_instalacion) {
            xajax_verAdj(xajax.getFormValues("form1"));
        }

        function guardar_transferencia(bode_cod_bode, tran, bodega_destino) {
            xajax_guardar_transferencia_repa(xajax.getFormValues("form1"), bode_cod_bode, tran, bodega_destino);
        }

        function reimpresion(ult) {
            xajax_reimpresion(xajax.getFormValues("form1"), ult);
        }

        function dowloand(ruta) {
            document.location = "dowloand.php?ruta=" + ruta;
        }


        // ---------------------------------------------------------
        // 				FIN ADJUNTOS SUEJO4967					  //
        // ---------------------------------------------------------
    </script>

    <!--DIBUJA FORMULARIO FILTRO-->

    <body>
        <div class="container-fluid">
            <form id="form1" name="form1" action="javascript:void(null);">
                <div id="divFormularioCabecera" class="table-responsive"></div>
                <div class="col-md-8" id="pestanas" style="float:left; width: 100%;">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li onclick="muestra_botones()" role="presentation" class="active"><a href="#divDirectorioMenu" aria-controls="divFormularioGenerales" role="tab" data-toggle="tab">DIRECTORIO</a></li>
                        <li onclick="muestra_botones()" role="presentation"><a href="#divRetencionMenu" aria-controls="divFormularioDatosSalario" role="tab" data-toggle="tab">RETENCION</a></li>
                        <li onclick="muestra_botones()" role="presentation"><a href="#divDiarioMenu" aria-controls="divCag" role="tab" data-toggle="tab">DIARIO</a></li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content" style="width: 100%;">
                        <div role="tabpanel" class="tab-pane active" id="divDirectorioMenu" style="width: 100%;">
                            <div id="divFormDir" class="table-responsive"></div>
                            <div id="divDir" class="table-responsive"></div>
                            <div id="divTotDir" class="table-responsive"></div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="divRetencionMenu">
                            <div id="divFormRet" class="table-responsive"></div>
                            <div id="divRet" class="table-responsive"></div>
                            <div id="divTotRet" class="table-responsive"></div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="divDiarioMenu">
                            <div id="divFormDiario" class="table-responsive"></div>
                            <div id="divDiario" class="table-responsive"></div>
                            <div id="divTotDiario" class="table-responsive"></div>
                        </div>
                    </div>
                </div>
                <div id="miModal_Diario" class="col-md-12"></div>
                <div style="width: 100%;">
                    <div id="miModalDistri"></div>
                </div>

                <div style="width: 100%;">
                    <div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>

                </div>

            </form>
        </div>
    </body>
    <script>
        genera_formulario();
    </script>
    <? /********************************************************************/ ?>
    <? /* NO MODIFICAR ESTA SECCION*/ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /********************************************************************/ ?>