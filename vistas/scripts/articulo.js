var tabla;

//funcion que se ejecuta al inicio
function init(){
   mostrarform(false);
   listar();

   $("#formulario").on("submit",function(e){
   	guardaryeditar(e);
   })

   //cargamos los items al celect categoria
   $.post("../ajax/articulo.php?op=selectCategoria", function(r){
   	$("#idcategoria").html(r);
   	$("#idcategoria").selectpicker('refresh');
   });
   $("#imagenmuestra").hide();
}

//funcion limpiar
function limpiar(){
	$("#codigo").val("");
	$("#nombre").val("");
	$("#descripcion").val("");
	$("#stock").val("");
	$("#imagenmuestra").attr("src","");
   	$("#imagenmuestra").hide();
	$("#imagenactual").val("");
	$("#print").hide();
	$("#idarticulo").val("");

	// ...otros campos limpiados
    $("#stock_s").prop("readonly", false).val(0);
    $("#stock_m").prop("readonly", false).val(0);
    $("#stock_l").prop("readonly", false).val(0);
    $("#stock_xl").prop("readonly", false).val(0);
}


//funcion mostrar formulario
function mostrarform(flag){
	limpiar();
	if(flag){
		$("#listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled",false);
		$("#btnagregar").hide();
	}else{
		$("#listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
	}
}

//cancelar form
function cancelarform(){
	limpiar();
	mostrarform(false);
}

//funcion listar
function listar(){
	tabla=$('#tbllistado').dataTable({
		"aProcessing": true,//activamos el procedimiento del datatable
		"aServerSide": true,//paginacion y filrado realizados por el server
		dom: 'Bfrtip',//definimos los elementos del control de la tabla
		buttons: [
                  //'copyHtml5',
                  'excelHtml5',
                  //'csvHtml5',
                  //'pdf'
		],
		"ajax":
		{
			url:'../ajax/articulo.php?op=listar',
			type: "get",
			dataType : "json",
			error:function(e){
				console.log(e.responseText);
			}
		},
		"bDestroy":true,
		"iDisplayLength":5,//paginacion
		"order":[[0,"desc"]]//ordenar (columna, orden)
	}).DataTable();
}
//funcion para guardaryeditar
function guardaryeditar(e){
     e.preventDefault();//no se activara la accion predeterminada 
     $("#btnGuardar").prop("disabled",true);
     var formData=new FormData($("#formulario")[0]);

     $.ajax({
     	url: "../ajax/articulo.php?op=guardaryeditar",
     	type: "POST",
     	data: formData,
     	contentType: false,
     	processData: false,

     	success: function(datos){
     		bootbox.alert(datos);
     		mostrarform(false);
     		tabla.ajax.reload();
     	}
     });

     limpiar();
}


function mostrar(idarticulo){
    $.post("../ajax/articulo.php?op=mostrar",{idarticulo : idarticulo},
        function(data,status)
        {
			debugger
			
            data=JSON.parse(data);
            mostrarform(true);

            $("#idcategoria").val(data.idcategoria);
            $("#idcategoria").selectpicker('refresh');
            $("#codigo").val(data.codigo);
            $("#nombre").val(data.nombre);
            $("#stock").val(data.stock);
            $("#descripcion").val(data.descripcion);
            $("#imagenmuestra").show();
            $("#imagenmuestra").attr("src","../files/articulos/"+data.imagen);
            $("#imagenactual").val(data.imagen);
            $("#idarticulo").val(data.idarticulo);
 

            // Si tu backend devuelve los campos de stock por talla, asígnalos:
            $("#stock_s").val(data.stock_s);
            $("#stock_m").val(data.stock_m);
            $("#stock_l").val(data.stock_l);
            $("#stock_xl").val(data.stock_xl);

            // BLOQUEAR los campos de tallas al editar (readonly)
            $("#stock_s").prop("readonly", true);
            $("#stock_m").prop("readonly", true);
            $("#stock_l").prop("readonly", true);
            $("#stock_xl").prop("readonly", true);
        })
}





//funcion para desactivar
function desactivar(idarticulo){
	bootbox.confirm("¿Esta seguro de desactivar este dato?", function(result){
		if (result) {
			$.post("../ajax/articulo.php?op=desactivar", {idarticulo : idarticulo}, function(e){
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function activar(idarticulo){
	bootbox.confirm("¿Esta seguro de activar este dato?" , function(result){
		if (result) {
			$.post("../ajax/articulo.php?op=activar" , {idarticulo : idarticulo}, function(e){
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

function generarbarcode(){
	codigo=$("#codigo").val();
	JsBarcode("#barcode",codigo);
	$("#print").show();

}

function imprimir(){
	$("#print").printArea();
}

function eliminar(idarticulo){
    bootbox.confirm("¿Está seguro de eliminar este artículo? Esta acción no se puede deshacer.", function(result){
        if(result){
            $.post("../ajax/articulo.php?op=eliminar", {idarticulo : idarticulo}, function(e){
                bootbox.alert(e);
                tabla.ajax.reload();
            });
        }
    });
}

// Vista previa de imagen al seleccionar archivo
document.getElementById('imagen').addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(evt){
            document.getElementById('imagenmuestra').style.display = 'block';
            document.getElementById('imagenmuestra').src = evt.target.result;
        }
        reader.readAsDataURL(file);
    } else {
        document.getElementById('imagenmuestra').style.display = 'none';
        document.getElementById('imagenmuestra').src = '';
    }
});

init();