var tabla;
var tablaarticulos;
var impuesto = 18;
var cont = 0;
var detalles = 0;
var datarticulos = [];

//funcion que se ejecuta al inicio
function init() {
  $('#tipo_comprobante').change(marcarImpuesto);
  mostrarform(false);
  listar();

  $('#formulario').on('submit', function (e) {
    guardaryeditar(e);
  });

  $.getJSON('../ajax/talla.php', function (r) {
    const filtroTalla = $('#filtroTalla');

    r.forEach((talla) => {
      const option = `<option value="${talla.idtalla}">${talla.nombre}</option>`;
      filtroTalla.append(option);
    });
  });

  $.post('../ajax/articulo.php?op=selectCategoria', function (r) {
    $('#idcategoria').html(r);
    $('#idcategoria').selectpicker('refresh');
    $('#filtroCategoria').html(r.replace('--Seleccione--', 'Todas'));
  });

  cargarClientes(null);

  $('#formCreacionRapidaCliente').on('submit', function (e) {
    e.preventDefault(); // evita recarga
    let formData = new FormData(this);
    const telefono = $('#telefonoCreacionRapida').val();

    $.ajax({
      url: '../ajax/persona.php?op=guardaryeditar',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function (respuesta) {
        cargarClientes(telefono);
        $('#formCreacionRapidaCliente')[0].reset();
        $('#modalCliente').modal('hide');
      },
      error: function (xhr, status, error) {
        $('#formCreacionRapidaCliente')[0].reset();
        console.error('Error en el envío:', error);
      },
    });
  });
}

function evaluarAbono() {
  const idtipocancelacion = parseInt($('#tipocancelacion').val());
  $('#abono').val('');

  if (idtipocancelacion == 1) {
    $('#colAbono').hide();
    $('#abono').removeAttr('required');
  } else {
    $('#colAbono').show();
    $('#abono').attr('required', 'true');
  }
}

function cargarClientes(telefono) {
  //cargamos los items al select cliente
  $.getJSON('../ajax/persona.php?op=listar&idtipopersona=1', function (r) {
    const selectCliente = $('#idcliente');
    selectCliente.html('');

    selectCliente.append(`<option value="">--Seleccione--</option>`);

    r.forEach((cliente) => {
      const numdoc =
        cliente.numdocumento !== ''
          ? cliente.tipodocumento + ': ' + cliente.numdocumento
          : 'Sin doc';
      const texto = cliente.nombre + ' (tel: ' + cliente.telefono + ')';
      const option = `<option value="${cliente.idpersona}">${texto}</option>`;

      //filtroproveedor.append(option);
      selectCliente.append(option);
    });

    if (telefono != null) {
      let idpersona = r.find((x) => x.telefono == telefono).idpersona;
      selectCliente.val(idpersona);
    }

    $('#idcliente').selectpicker('refresh');
  });
}

//funcion limpiar
function limpiar() {
  $('#idcliente').val('');
  $('#cliente').val('');
  $('#serie_comprobante').val('');
  $('#num_comprobante').val('');
  $('#impuesto').val('');

  $('#total_venta').val('');
  $('.filas').remove();
  $('#total').html('0');

  //obtenemos la fecha actual
  var now = new Date();
  var day = ('0' + now.getDate()).slice(-2);
  var month = ('0' + (now.getMonth() + 1)).slice(-2);
  var today = now.getFullYear() + '-' + month + '-' + day;
  $('#fecha_hora').val(today);

  //marcamos el primer tipo_documento
  $('#tipo_comprobante').val('Boleta');
  $('#tipo_comprobante').selectpicker('refresh');
}

//funcion mostrar formulario
function mostrarform(flag) {
  limpiar();
  if (flag) {
    $('#listadoregistros').hide();
    $('#formularioregistros').show();
    //$("#btnGuardar").prop("disabled",false);
    $('#btnagregar').hide();
    listarArticulos();

    $('#btnGuardar').attr('disabled', 'true');
    $('#btnCancelar').show();
    detalles = 0;
    $('#btnAgregarArt').show();
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
      aServerSide: true, //paginacion y filrado realizados por el server
      dom: 'Bfrtip', //definimos los elementos del control de la tabla
      buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdf'],
      ajax: {
        url: '../ajax/venta.php?op=listar',
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

function listarArticulos() {
  let valorFiltroTalla = $('#filtroTalla').val();
  let textoTalla = '(' + $('#filtroTalla option:selected').text() + ')';

  if (valorFiltroTalla === '') {
    textoTalla = '';
  }

  $('.tallafiltrada').text(textoTalla);
  tablaarticulos = $('#tblarticulos')
    .dataTable({
      aProcessing: true, //activamos el procedimiento del datatable
      aServerSide: true, //paginacion y filrado realizados por el server
      dom: 'Bfrtip', //definimos los elementos del control de la tabla
      buttons: [],
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
            if (!datarticulos.some((a) => a.idarticulo == data.idarticulo)) {
              datarticulos.push(data);
            }
            return (
              '<button class="btn btn-warning btn-agregar-articulo" data-idarticulo="' +
              data.idarticulo +
              '">' +
              '<span class="fa fa-plus"></span></button>'
            );
          },
        },
        {
          data: null,
          orderable: false,
          render: function (data, type, row, meta) {
            var idarticulo = data.idarticulo;
            var select =
              '<select class="form-control input-sm select-talla" style="width: 80px;" data-idarticulo="' +
              idarticulo +
              '">';
            select += '<option value="">-</option>';

            let listatallas = data.detallestock;

            for (var i = 0; i < listatallas.length; i++) {
              select +=
                '<option value="' +
                listatallas[i].idtalla +
                '">' +
                listatallas[i].talla +
                '</option>';
            }
            select += '</select>';
            return select;
          },
        },
        {
          data: null,
          render: function (data, type, row, meta) {
            return (
              '<img src="../files/articulos/' + data.imagen + '" height="50px" width="50px"></img>'
            );
          },
        },
        { data: 'nombre' },
        { data: 'categoria' },
        { data: 'codigo' },
        {
          data: null,
          render: function (data, type, row, meta) {
            var texto = data.stock;

            /*
            let listatallas = data.detallestock;

            if (data.stock > 0) {
              texto +=
                '<div style="width: 100%; max-width: 100px; overflow-x: scroll; white-space: nowrap;">';

              for (var i = 0; i < listatallas.length; i++) {
                if (listatallas[i].stock > 0) {
                  texto += listatallas[i].talla + ': ' + listatallas[i].stock;

                  if (i < listatallas.length - 1) {
                    texto += ' - ';
                  }
                }
              }
              texto += '</div>';
            }
              */

            return texto;
          },
        },
        { data: 'precioventa' },
      ],
      bDestroy: true,
      iDisplayLength: 10, //paginacion
      order: [], //ordenar (columna, orden)
    })
    .DataTable();
  $('#tblarticulos tbody').on('click', '.btn-agregar-articulo', function () {
    var boton = $(this);
    var tr = boton.closest('tr');
    var idarticulo = boton.data('idarticulo');
    var selectTalla = tr.find('.select-talla');
    var idtalla = selectTalla.val();

    // Buscar el artículo y la talla en el array datarticulos
    var articuloObj = datarticulos.find((item) => item.idarticulo == idarticulo);

    if (!idtalla) {
      alert('No seleccionó una talla');
      return;
    }

    var stockObj = articuloObj.detallestock.find((item) => item.idtalla == idtalla);
    var stock = stockObj ? stockObj.stock : 0;

    if (stock <= 0) {
      alert('No hay stock suficiente');
      return;
    }

    // Aquí recupera el resto de datos para agregarDetalle...
    var articulo = tr.find('td').eq(3).text();
    var precio_venta = parseFloat(tr.find('td').eq(6).text());
    var imagen = datarticulos.find((item) => item.idarticulo == idarticulo).imagen;

    agregarDetalle(idarticulo, articulo, precio_venta, imagen);
  });
}
//funcion para guardaryeditar
function guardaryeditar(e) {
  e.preventDefault(); //no se activara la accion predeterminada
  //$("#btnGuardar").prop("disabled",true);
  var formData = new FormData($('#formulario')[0]);

  $.ajax({
    url: '../ajax/venta.php?op=guardaryeditar',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,

    success: function (datos) {
      bootbox.alert(datos);
      mostrarform(false);
      listar();
    },
  });

  limpiar();
}

function mostrar(idventa) {
  $.post('../ajax/venta.php?op=mostrar', { idventa: idventa }, function (data, status) {
    data = JSON.parse(data);
    mostrarform(true);

    $('#idcliente').val(data.idcliente);
    $('#idcliente').selectpicker('refresh');
    $('#tipo_comprobante').val(data.tipo_comprobante);
    $('#tipo_comprobante').selectpicker('refresh');
    $('#serie_comprobante').val(data.serie_comprobante);
    $('#num_comprobante').val(data.num_comprobante);
    $('#fecha_hora').val(data.fecha);
    $('#impuesto').val(data.impuesto);
    $('#idventa').val(data.idventa);

    //ocultar y mostrar los botones
    $('#btnGuardar').attr('disabled', 'true');
    $('#btnCancelar').show();
    $('#btnAgregarArt').hide();
  });
  $.post('../ajax/venta.php?op=listarDetalle&id=' + idventa, function (r) {
    $('#detalles').html(r);
  });
}

//funcion para desactivar
function anular(idventa) {
  bootbox.confirm('¿Esta seguro de desactivar este dato?', function (result) {
    if (result) {
      $.post('../ajax/venta.php?op=anular', { idventa: idventa }, function (e) {
        bootbox.alert(e);
        tabla.ajax.reload();
      });
    }
  });
}

function marcarImpuesto() {
  var tipo_comprobante = $('#tipo_comprobante option:selected').text();
  if (tipo_comprobante == 'Factura') {
    $('#impuesto').val(impuesto);
  } else {
    $('#impuesto').val('0');
  }
}

function verificarBotonVenta() {
  // Busca todas las filas de productos
  var filas = $('#detalles tr.filas');
  var habilitar = false;

  filas.each(function () {
    var cantidad = parseInt($(this).find('input[name="cantidad[]"]').val()) || 0;
    var stock = parseInt($(this).find('td').eq(4).text()) || 0;
    // Si hay al menos una fila con cantidad válida y stock suficiente, habilita el botón
    if (cantidad > 0 && cantidad <= stock) {
      habilitar = true;
    }
    // Si encuentra una fila sin stock, no habilita el botón
    if (stock === 0 || cantidad === 0 || cantidad > stock) {
      habilitar = false;
      return false; // corta el each
    }
  });

  if (filas.length === 0) habilitar = false;

  $('#btnRealizarVenta').prop('disabled', !habilitar);
}

function agregarDetalle(idarticulo, articulo, precio_venta, imagen) {
  var selectTalla = $('.select-talla[data-idarticulo="' + idarticulo + '"]');
  var idtalla = selectTalla.val();
  var nombreTalla = selectTalla.find('option:selected').text();

  if (!idtalla) {
    alert('Debe seleccionar una talla para el producto');
    return;
  }

  var articuloObj = datarticulos.find((item) => item.idarticulo == idarticulo);
  if (!articuloObj) {
    alert('Artículo no encontrado');
    return;
  }
  var stockObj = articuloObj.detallestock.find((item) => item.idtalla == idtalla);
  if (!stockObj) {
    alert('No hay stock para esta talla');
    return;
  }
  var stock = stockObj.stock;

  var filaId = 'fila' + idarticulo + '_' + idtalla;
  var filaExistente = $('#' + filaId);

  if (filaExistente.length > 0) {
    // Ya existe, aumentar cantidad y actualizar subtotal
    var cantidadInput = filaExistente.find('input[name="cantidad[]"]');
    var cantidadActual = parseInt(cantidadInput.val());
    var nuevaCantidad = cantidadActual + 1;
    if (nuevaCantidad > stock) {
      alert('No hay suficiente stock para esta talla');
      return;
    }
    cantidadInput.val(nuevaCantidad);
    actualizarSubtotal(filaId);
  } else {
    // No existe, agrega la fila nueva
    var cantidad = 1;
    var descuento = 0;
    var subtotal = precio_venta * cantidad - descuento;

    var fila =
      '<tr class="filas" id="' +
      filaId +
      '">' +
      '<td><button type="button" class="btn btn-danger" onclick="eliminarDetalle(\'' +
      filaId +
      '\')">X</button></td>' +
      '<td><img src="../files/articulos/' +
      imagen +
      '" width="50" height="50"></td>' +
      '<td><input type="hidden" name="idarticulo[]" value="' +
      idarticulo +
      '">' +
      articulo +
      '</td>' +
      '<td><input type="hidden" name="idtalla[]" value="' +
      idtalla +
      '">' +
      nombreTalla +
      '</td>' +
      '<td>' +
      stock +
      '</td>' +
      '<td><input type="number" class="form-control cantidad" name="cantidad[]" value="' +
      cantidad +
      '" min="1" max="' +
      stock +
      '" onchange="actualizarSubtotal(\'' +
      filaId +
      '\')"></td>' +
      '<td><input type="hidden" name="precio_venta[]" value="' +
      precio_venta +
      '" readonly> ' +
      precio_venta +
      '</td>' +
      '<td ><input type="number" class="form-control descuento" name="descuento[]" value="' +
      descuento +
      '" onchange="actualizarSubtotal(\'' +
      filaId +
      '\')"></td>' +
      '<td><span id="subtotal' +
      filaId +
      '" name="subtotal">' +
      subtotal +
      '</span></td>' +
      '</tr>';
    $('#detalles').append(fila);
    modificarSubtotales();
    verificarBotonVenta();
  }
}

function actualizarSubtotal(filaId) {
  var fila = $('#' + filaId);
  var cantidadInput = fila.find('input[name="cantidad[]"]');
  var cantidad = parseInt(cantidadInput.val()) || 0;
  var precio = parseFloat(fila.find('input[name="precio_venta[]"]').val()) || 0;
  var descuentoInput = fila.find('input[name="descuento[]"]');
  var descuento = parseFloat(descuentoInput.val()) || 0;
  var stock = parseInt(fila.find('td').eq(4).text()) || 0; // Stock desde la columna

  // Validar cantidad contra stock
  if (cantidad > stock) {
    cantidad = stock;
    cantidadInput.val(stock);
    alert('No puedes ingresar una cantidad mayor al stock disponible');
  } else if (cantidad < 1) {
    cantidad = 1;
    cantidadInput.val(1);
  }

  // Validar descuento no mayor que el precio
  if (descuento > precio) {
    descuento = precio;
    descuentoInput.val(precio);
    alert('El descuento no puede ser mayor que el precio de venta');
  } else if (descuento < 0) {
    descuento = 0;
    descuentoInput.val(0);
  }

  var subtotal = cantidad * precio - descuento;
  fila.find('span[name="subtotal"]').text(subtotal);
  modificarSubtotales();
  verificarBotonVenta();
}
function modificarSubtotales() {
  var cant = document.getElementsByName('cantidad[]');
  var prev = document.getElementsByName('precio_venta[]');
  var desc = document.getElementsByName('descuento[]');
  var sub = document.getElementsByName('subtotal');

  for (var i = 0; i < cant.length; i++) {
    var inpV = cant[i];
    var inpP = prev[i];
    var inpS = sub[i];
    var des = desc[i];

    inpS.value = inpV.value * inpP.value - des.value;
    document.getElementsByName('subtotal')[i].innerHTML = inpS.value;
  }

  calcularTotales();
}

function calcularTotales() {
  var sub = document.getElementsByName('subtotal');
  var total = 0.0;

  for (var i = 0; i < sub.length; i++) {
    total += document.getElementsByName('subtotal')[i].value;
  }
  $('#total').html('S/.' + total);
  $('#total_venta').val(total);
  evaluar();
}

function evaluar() {
  if (detalles > 0) {
    $('#btnGuardar').removeAttr('disabled');
  } else {
    $('#btnGuardar').attr('disabled', 'true');
    cont = 0;
  }
}

function eliminarDetalle(filaId) {
  $('#' + filaId).remove();
  modificarSubtotales(); // Si tienes esta función para actualizar el total, inclúyela aquí
  verificarBotonVenta();
}

init();
