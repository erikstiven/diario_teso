<? /********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? include_once('../_Modulo.inc.php');?>
<? include_once(HEADER_MODULO);?>
<? if ($ejecuta) { ?>
<? /********************************************************************/ ?>
<?
	if(isset($_REQUEST['mOp'])) $mOp=$_REQUEST['mOp'];
		else $mOp='';
        $array = $_GET['array'];
        list($factura, $sucu, $clpv, $idempresa, $tran, $det, $tipo, $ccli, $coti, $mone, $coti_ext ) = explode(",", $array);
        // echo 'sdsd '.$tipo;
?>
<script>
      	function cerrar_ventana(){
      		CloseAjaxWin();
      	}        
        
        function cargar(){
			       parent.xajax_agrega_modifica_grid_dir(0, xajax.getFormValues("form1"), 0, '<?=$idempresa?>', '<?=$coti?>' , '<?=$mone?>', '<?=$coti_ext?>' );
        }
      
		
        function cerrar_ventana(){
      		CloseAjaxWin();
      	}
		
		function marcar(source) {
            checkboxes = document.getElementsByTagName('input'); //obtenemos todos los controles del tipo Input
            for (i = 0; i < checkboxes.length; i++) //recoremos todos los controles
            {
                if (checkboxes[i].type == "checkbox") //solo si es un checkbox entramos
                {
                    checkboxes[i].checked = source.checked; //si es un checkbox le damos el valor del checkbox que lo llamó (Marcar/Desmarcar Todos)
                }
            }
            xajax_cargar_tot( xajax.getFormValues("form1") );
        }

        function cargar_tot(){
			xajax_cargar_tot( xajax.getFormValues("form1") );
        }
		
		function pintaValor(id, val){
			document.getElementById(id).value = val;
			xajax_calculo( xajax.getFormValues('form1'));
		}

		function mascara(o,f){  
			v_obj=o;  
			v_fun=f;  
			setTimeout("execmascara()",1);  
		}  
		
		function execmascara(){   
			v_obj.value=v_fun(v_obj.value);
		}
	
		function cpf(v){     
			v=v.replace(/([^0-9\.]+)/g,''); 
			v=v.replace(/^[\.]/,''); 
			v=v.replace(/[\.][\.]/g,''); 
			v=v.replace(/\.(\d)(\d)(\d)/g,'.$1$2'); 
			v=v.replace(/\.(\d{1,2})\./g,'.$1'); 
			v = v.toString().split('').reverse().join('').replace(/(\d{3})/g,'$1,');    
			v = v.split('').reverse().join('').replace(/^[\,]/,''); 
			return v;  
		}  
</script>
<!-- Divs contenedores!-->
<div align="center">
    <form id="form1" name="form1" action="javascript:void(null);">
      <table align="center" border="0" cellpadding="2" cellspacing="0" width="100%">
        <tr>
          	<td valign="top" align="center">
                    <div id="divFormularioDetalle"></div>
         	</td>
        </tr>
        </tr>
      </table>
     </form>
</div>
<script>
xajax_reporte_facturas( '<?=$idempresa?>', '<?=$sucu?>', '<?=$clpv?>',  '<?=$factura?>', '<?=$tran?>', '<?=$det?>', '<?=$tipo?>', '<?=$ccli?>');
</script>
<? /********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /********************************************************************/ ?>