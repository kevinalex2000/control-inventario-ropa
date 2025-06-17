var tabla;

//funcion que se ejecuta al inicio
function init() {
  mostrarform(false);
  listar();

  $('#formulario').on('submit', function (e) {
    guardaryeditar(e);
  });

  $('#imagen').on('change', function (e) {
    const file = e.target.files[0];
    const $preview = $('#imagenmuestra');

    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => $preview.show().attr('src', e.target.result);
      reader.readAsDataURL(file);
    } else {
      $preview.hide().attr('src', '');
    }
  });

  $.post('../ajax/articulo.php?op=selectCategoria', function (r) {
    $('#idcategoria').html(r);
    $('#idcategoria').selectpicker('refresh');
    $('#filtroCategoria').html('<option value="">Todas</option>' + r.replace('--Seleccione--', 'Todas'));

  });
  
  $('#imagenmuestra').hide();

  $.getJSON('../ajax/talla.php', function (r) {
    const $tbody = $('#idtblstockxtallas tbody');
    $tbody.empty();

    const $filtroTalla = $('#filtroTalla');

    r.forEach((talla) => {
      const fila = `
        <tr>
          <td>${talla.nombre}</td>
          <td>
            <input type="number" 
              class="form-control text-center stock-talla" 
              data-idtalla="${talla.idtalla}" 
              name="stock_${talla.nombre}" 
              id="stock_${talla.nombre}" 
              min="0" 
              value="0" />
          </td>
        </tr>
      `;
      $tbody.append(fila);

      const option = `<option value="${talla.idtalla}">${talla.nombre}</option>`;
      $filtroTalla.append(option);
    });
  });

  $('#precio_venta').on('input', function () {
    let precio = parseFloat($(this).val());
    if (isNaN(precio) || precio <= 0) {
      $('#error_precio').show();
      $(this).addClass('is-invalid');
    } else {
      $('#error_precio').hide();
      $(this).removeClass('is-invalid');
    }
  });
}

//funcion limpiar
function limpiar() {
  $('#codigo').val('');
  $('#nombre').val('');
  $('#descripcion').val('');
  $('#stock').val('');
  $('#imagenmuestra').attr('src', '');
  $('#imagenmuestra').hide();
  $('#imagenactual').val('');
  $('#print').hide();
  $('#idarticulo').val('');
  $('#idcategoria').val('').selectpicker('refresh');
  $('.stock-talla').val(0);
  $('.stock-talla').prop('readonly', false);
}

//funcion mostrar formulario
function mostrarform(flag) {
  limpiar();
  $('#imagen').attr('required', 'true');
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
  let valorFiltroTalla = $('#filtroTalla').val();
  let textoTalla = '(' + $('#filtroTalla option:selected').text() + ')';

  if (valorFiltroTalla === '') {
    textoTalla = '';
  }

  $('.tallafiltrada').text(textoTalla);
  tabla = $('#tbllistado')
    .dataTable({
      aProcessing: true, //activamos el procedimiento del datatable
      aServerSide: true, //paginacion y filrado realizados por el server
      searching: true,
      dom: 'Bfrtip', //definimos los elementos del control de la tabla
      buttons: [
        {
          extend: 'excelHtml5',
          filename: ArmarNombreDeArchivo('Reporte_Articulos'),
          exportOptions: {
            columns: [1, 2, 3, 4, 6, 7, 8], // Indica las columnas que SÍ quieres exportar
          },
        },
        //'copyHtml5',
        //'csvHtml5',
        //'pdf'
      ],
      ajax: {
        url: '../ajax/articulo.php?op=listar',
        data: function (d) {
          // Agrega tus filtros personalizados al objeto "d"
          d.idcategoria = $('#filtroCategoria').val();
          d.idtalla = valorFiltroTalla;
          d.condicion = $('#filtroEstado').val();
        },
        type: 'get',
        dataType: 'json',
        error: function (e) {
          console.log(e.responseText);
        },
      },
      bDestroy: true,
      iDisplayLength: 5, //paginacion
      order: [[0, 'desc']], //ordenar (columna, orden)
    })
    .DataTable();
}
//funcion para guardaryeditar
function guardaryeditar(e) {
  e.preventDefault(); //no se activara la accion predeterminada

  var precio_venta = parseFloat($('#precio_venta').val());
  if (isNaN(precio_venta) || precio_venta <= 0) {
    $('#error_precio').show();
    $('#precio_venta').addClass('is-invalid').focus();
    return false; // No envía el formulario, pero NO limpia los campos ni deshabilita el botón
  }
  
  $('#btnGuardar').prop('disabled', true);
  let formData = new FormData($('#formulario')[0]);
  let stockxTalla = [];

  $('#formulario .stock-talla').each(function () {
    let idtalla = $(this).data('idtalla');
    let valor = parseFloat($(this).val());

    stockxTalla.push({
      idtalla: idtalla,
      stock: valor,
    });
  });

  let data = {
    nombre: formData.get('nombre'),
    codigo: formData.get('codigo'),
    idcategoria: parseInt(formData.get('idcategoria')),
    descripcion: formData.get('descripcion'),
    precio_venta: parseFloat(formData.get('precio_venta')),
    imagen: formData.get('imagen'),
    stockxtalla: stockxTalla,
  };

  $.ajax({
    url: '../ajax/articulo.php?op=guardaryeditar',
    type: 'POST',
    data: data,
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

function mostrar(idarticulo) {
  let loaderId = CrearPantallaCargaUnica();
  $.post('../ajax/articulo.php?op=mostrar', { idarticulo: idarticulo }, function (data, status) {
    data = JSON.parse(data);
    mostrarform(true);

    $('#idcategoria').val(data.idcategoria);
    $('#idcategoria').selectpicker('refresh');
    $('#codigo').val(data.codigo);
    $('#nombre').val(data.nombre);
    $('#descripcion').val(data.descripcion);
    $('#imagenmuestra').show();
    $('#imagenmuestra').attr('src', '../files/articulos/' + data.imagen);
    $('#imagenactual').val(data.imagen);
    $('#idarticulo').val(data.idarticulo);
    $('#imagen').removeAttr('required');

    data.detallestock.forEach(function (item) {
      $('[data-idtalla="' + item.idtalla + '"]').val(item.stock);
    });

    // BLOQUEAR los campos de tallas al editar (readonly)
    $('.stock-talla').prop('readonly', true);
  }).always(function () {
    // Eliminar la pantalla de carga cuando termina la solicitud
    $('#' + loaderId).remove();
  });
}

//funcion para desactivar
function desactivar(idarticulo) {
  bootbox.confirm('¿Esta seguro de desactivar este dato?', function (result) {
    if (result) {
      $.post('../ajax/articulo.php?op=desactivar', { idarticulo: idarticulo }, function (e) {
        bootbox.alert(e);
        tabla.ajax.reload();
      });
    }
  });
}

function activar(idarticulo) {
  bootbox.confirm('¿Esta seguro de activar este dato?', function (result) {
    if (result) {
      $.post('../ajax/articulo.php?op=activar', { idarticulo: idarticulo }, function (e) {
        bootbox.alert(e);
        tabla.ajax.reload();
      });
    }
  });
}

function generarbarcode() {
  codigo = $('#codigo').val();
  JsBarcode('#barcode', codigo);
  $('#print').show();
}

function imprimir() {
  $('#print').printArea();
}

function eliminar(idarticulo) {
  bootbox.confirm(
    '¿Está seguro de eliminar este artículo? Esta acción no se puede deshacer.',
    function (result) {
      if (result) {
        $.post('../ajax/articulo.php?op=eliminar', { idarticulo: idarticulo }, function (e) {
          debugger;
          bootbox.alert(e);
          tabla.ajax.reload();
        });
      }
    }
  );
}

function buscarEnTabla(value) {
  tabla.search(value).draw();
}

init();
