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
    $('#filtroCategoria').html(r.replace('--Seleccione--', 'Todas'));
  });

  $('#imagenmuestra').hide();

  $.getJSON('../ajax/talla.php', function (r) {
    const tbody = $('#idtblstockxtallas tbody');
    tbody.empty();

    const filtroTalla = $('#filtroTalla');

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
              step="1"
              min="0" 
              placeholder="0"
              oninput="this.value = Math.floor(this.value)"
              />
          </td>
        </tr>
      `;
      tbody.append(fila);

      const option = `<option value="${talla.idtalla}">${talla.nombre}</option>`;
      filtroTalla.append(option);
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
  $('#imagen').val('');
  $('#print').hide();
  $('#idarticulo').val('');
  $('#idcategoria').val('').selectpicker('refresh');
  $('#precio_venta').val('');
  $('.stock-talla').val('');
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
        type: 'get',
        dataType: 'json',
        dataSrc: '',
        data: function (d) {
          d.idcategoria = $('#filtroCategoria').val();
          d.idtalla = valorFiltroTalla;
          d.condicion = $('#filtroEstado').val();
        },
      },
      columns: [
        {
          data: null,
          orderable: false,
          render: function (data, type, row, meta) {
            return data.condicion == 1
              ? '<button class="btn btn-warning btn-xs" onclick="mostrar(' +
                  data.idarticulo +
                  ')"><i class="fa fa-pencil"></i></button>' +
                  ' ' +
                  '<button class="btn btn-danger btn-xs" onclick="desactivar(' +
                  data.idarticulo +
                  ')"><i class="fa fa-close"></i></button>'
              : '<button class="btn btn-warning btn-xs" onclick="mostrar(' +
                  data.idarticulo +
                  ')"><i class="fa fa-pencil"></i></button>' +
                  ' ' +
                  '<button class="btn btn-primary btn-xs" onclick="activar(' +
                  data.idarticulo +
                  ')"><i class="fa fa-check"></i></button>' +
                  ' ' +
                  '<button class="btn btn-danger btn-xs" onclick="eliminar(' +
                  data.idarticulo +
                  ')"><i class="fa fa-trash"></i></button>';
          },
        },
        { data: 'nombre' },
        { data: 'categoria' },
        { data: 'codigo' },
        { data: 'stock' },
        {
          data: null,
          render: function (data, type, row, meta) {
            return (
              '<img src="../files/articulos/' + data.imagen + '" height="50px" width="50px"></img>'
            );
          },
        },
        { data: 'descripcion' },
        { data: 'precioventa' },
        {
          data: null,
          render: function (data, type, row, meta) {
            return data.condicion
              ? '<span class="label bg-green">Activado</span>'
              : '<span class="label bg-red">Desactivado</span>';
          },
        },
      ],
      bDestroy: true,
      iDisplayLength: 20,
      order: [[0, 'desc']],
    })
    .DataTable();
}

//funcion para guardaryeditar
function guardaryeditar(e) {
  e.preventDefault(); //no se activara la accion predeterminada

  var nombre = $('#nombre').val().trim();
  var codigo = $('#codigo').val().trim();
  var idarticulo = $('#idarticulo').val();

  var precio_venta = parseFloat($('#precio_venta').val());
  if (isNaN(precio_venta) || precio_venta <= 0) {
    $('#error_precio').show();
    $('#precio_venta').addClass('is-invalid').focus();
    return false;
  }

  // Validar nombre/código antes de guardar
  $.ajax({
    url: '../ajax/articulo.php?op=validarNombreCodigo',
    type: 'POST',
    data: { nombre: nombre, codigo: codigo, idarticulo: idarticulo },
    dataType: 'json',

    success: function (res) {
      if (res.codigo) {
        bootbox.alert('El código ya está siendo utilizado por otro artículo');
        return false;
      }
      if (res.nombre) {
        bootbox.alert('El nombre ya está siendo utilizado por otro artículo');
        return false;
      } else {
        // Ahora sí envía el formulario normalmente
        $('#btnGuardar').prop('disabled', true);
        let formData = new FormData($('#formulario')[0]);
        let stockxTalla = [];

        $('#formulario .stock-talla').each(function () {
          let idtalla = $(this).data('idtalla');
          let valor = $(this).val() !== '' ? parseFloat($(this).val()) : 0;

          stockxTalla.push({
            idtalla: idtalla,
            stock: valor,
          });
        });

        formData.append('stockxtalla', JSON.stringify(stockxTalla));

        $.ajax({
          url: '../ajax/articulo.php?op=guardaryeditar',
          type: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function (datos) {
            bootbox.alert(datos);
            mostrarform(false);
            tabla.ajax.reload();
          },
          error: function (e) {},
        });

        limpiar();
      }
    },
  });
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
    $('#precio_venta').val(data.precioventa);
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
          bootbox.alert(e);
          tabla.ajax.reload();
        });
      }
    }
  );
}

init();
