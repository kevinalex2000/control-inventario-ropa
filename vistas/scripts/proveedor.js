var tabla;

//funcion que se ejecuta al inicio
function init() {
  mostrarform(false);
  listar();

  $('#formulario').on('submit', function (e) {
    guardaryeditar(e);
  });
}

//funcion limpiar
function limpiar() {
  $('#nombre').val('');
  $('#num_documento').val('');
  $('#direccion').val('');
  $('#telefono').val('');
  $('#email').val('');
  $('#idpersona').val('');
  $('#tipo_documento').val('');
  $('#tipo_documento').val('').selectpicker('refresh');
}

//funcion mostrar formulario
function mostrarform(flag) {
  limpiar();
  if (flag) {
    $('#listadoregistros').hide();
    $('#formularioregistros').show();
    $('#btnGuardar').prop('disabled', false);
    $('#btnagregar').hide();
  } else {
    $('#listadoregistros').show();
    $('#formularioregistros').hide();
    $('#btnagregar').show();
  }
}

//cancelar form
function cancelarform() {
  limpiar();
  mostrarform(false);
}

//funcion listar
function listar() {
  tabla = $('#tbllistado')
    .dataTable({
      aProcessing: true, //activamos el procedimiento del datatable
      aServerSide: false,
      dom: 'Bfrtip', //definimos los elementos del control de la tabla
      buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdf'],
      ajax: {
        url: '../ajax/persona.php?op=listar&idtipopersona=2',
        type: 'get',
        dataType: 'json',
        dataSrc: '',
      },
      bDestroy: true,
      iDisplayLength: 20, //paginacion
      order: [[0, 'desc']], //ordenar (columna, orden)
      columns: [
        {
          data: null,
          render: function (data, type, row) {
            console.log(row);
            let render = `
            <button class="btn btn-warning btn-xs" onclick="mostrar(${row.idpersona})"><i class="fa fa-pencil"></i></button>
            <button class="btn btn-danger btn-xs" onclick="eliminar(${row.idpersona})"><i class="fa fa-trash"></i></button>`;
            return render;
          },
        },
        { data: 'nombre' },
        { data: 'tipodocumento' },
        { data: 'numdocumento' },
        { data: 'telefono' },
        { data: 'email' },
      ],
    })
    .DataTable();
}
//funcion para guardaryeditar
function guardaryeditar(e) {
  e.preventDefault(); //no se activara la accion predeterminada
  $('#btnGuardar').prop('disabled', true);
  var formData = new FormData($('#formulario')[0]);

  $.ajax({
    url: '../ajax/persona.php?op=guardaryeditar',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,

    success: function (datos) {
      bootbox.alert(datos);
      mostrarform(false);
      tabla.ajax.reload();
    },
  });

  limpiar();
}

function mostrar(idpersona) {
  $.post('../ajax/persona.php?op=mostrar', { idpersona: idpersona }, function (data, status) {
    data = JSON.parse(data);
    mostrarform(true);

    $('#nombre').val(data.nombre);
    $('#tipo_documento').val(data.tipo_documento);
    $('#tipo_documento').selectpicker('refresh');
    $('#num_documento').val(data.num_documento);
    $('#direccion').val(data.direccion);
    $('#telefono').val(data.telefono);
    $('#email').val(data.email);
    $('#idpersona').val(data.idpersona);
  });
}

//funcion para desactivar
function eliminar(idpersona) {
  bootbox.confirm('¿Esta seguro de eliminar este dato?', function (result) {
    if (result) {
      $.post('../ajax/persona.php?op=eliminar', { idpersona: idpersona }, function (e) {
        bootbox.alert(e);
        tabla.ajax.reload();
      });
    }
  });
}

init();
