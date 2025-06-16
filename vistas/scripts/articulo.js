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
	$('#idcategoria').val('').selectpicker('refresh');

	// ...otros campos limpiados
    $(".stock-talla").prop("readonly", false);
}


//funcion mostrar formulario
function mostrarform(flag){
	limpiar();
	$("#imagen").attr("required","true");
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
			{
				extend: 'excelHtml5',
				filename: function() {
					// Obtener fecha y hora actual en formato YYYY-MM-DD_HH-MM-SS
					var date = new Date();
					var year = date.getFullYear();
					var month = ("0" + (date.getMonth() + 1)).slice(-2);
					var day = ("0" + date.getDate()).slice(-2);
					var hours = ("0" + date.getHours()).slice(-2);
					var minutes = ("0" + date.getMinutes()).slice(-2);
					var seconds = ("0" + date.getSeconds()).slice(-2);
					
					return 'Reporte_Articulos_' + year + '-' + month + '-' + day + '_' + hours + '-' + minutes + '-' + seconds;
				},
				exportOptions: {
					columns: [1, 2, 3, 4, 6,7] // Indica las columnas que SÍ quieres exportar
				}
			}
			//'copyHtml5',
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
	let loaderId = crearPantallaCargaUnica();
    $.post("../ajax/articulo.php?op=mostrar",{idarticulo : idarticulo},
        function(data,status)
        {
            data=JSON.parse(data);
            mostrarform(true);

            $("#idcategoria").val(data.idcategoria);
            $("#idcategoria").selectpicker('refresh');
            $("#codigo").val(data.codigo);
            $("#nombre").val(data.nombre);
            $("#descripcion").val(data.descripcion);
            $("#imagenmuestra").show();
            $("#imagenmuestra").attr("src","../files/articulos/"+data.imagen);
            $("#imagenactual").val(data.imagen);
            $("#idarticulo").val(data.idarticulo);
			$("#imagen").removeAttr("required");

			data.detallestock.forEach(function(item) {
				$('[data-idtalla="'+item.idtalla+'"]').val(item.stock);				
			});

            // BLOQUEAR los campos de tallas al editar (readonly)
            $(".stock-talla").prop("readonly", true);
        })
		.always(function() {
			// Eliminar la pantalla de carga cuando termina la solicitud
			$('#' + loaderId).remove();
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
				debugger;
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