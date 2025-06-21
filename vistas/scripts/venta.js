var tabla;
var tablaarticulos;
var impuesto = 18;
var cont = 0;
var detalles = 0;
var datarticulos = [];

//funcion que se ejecuta al inicio
function init() {
  $('#abono').on('input', function () {
    let subtotal = parseFloat($('#total_venta').val()) || 0;
    let abono = parseFloat($(this).val()) || 0;
    if (abono > subtotal) {
      alert('El adelanto no puede ser mayor que el total de la venta.');
      $(this).val(0);
      abono = 0;
    }
    let pendiente = subtotal - abono;
    $('#saldo_pendiente').val(pendiente.toFixed(2));
  });

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

  $('#btnEntregar').click(function () {
    Entregar($('#idventa').val());
  });

  $('#btnDeuda').click(function () {
    CompletarPago($('#idventa').val());
  });
}

function evaluarAbono() {
  const idtipocancelacion = parseInt($('#tipocancelacion').val());
  $('#abono').val('');

  if (idtipocancelacion == 1) {
    $('#colAbono').hide();
    $('#colPendiente').hide();
    $('#abono').val('');
    $('#abono').removeAttr('required');
  } else {
    $('#colAbono').show();
    $('#colPendiente').show();
    $('#abono').attr('required', 'true');
  }
}

function cargarClientes(telefono) {
  //cargamos los items al select cliente
  $.getJSON('../ajax/persona.php?op=listar&idtipopersona=1', function (r) {
    const selectCliente = $('#idcliente');
    const filtroCliente = $('#filtroCliente');
    selectCliente.html('');
    selectCliente.append(`<option value="">--Seleccione--</option>`);

    r.forEach((cliente) => {
      const numdoc =
        cliente.numdocumento !== ''
          ? cliente.tipodocumento + ': ' + cliente.numdocumento
          : 'Sin doc';
      const texto = cliente.nombre + ' (Tel: ' + cliente.telefono + ')';
      const option = `<option value="${cliente.idpersona}">${texto}</option>`;

      //filtroproveedor.append(option);
      filtroCliente.append(option);
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

  $('#idcliente').val('');
  $('#idcliente').removeAttr('disabled');
  $('#idcliente').selectpicker('refresh');

  $('#abono').val('');
  $('#abono').removeAttr('disabled');
  $('#tipocancelacion').val('1');
  $('#tipocancelacion').removeAttr('disabled');
  $('#tipocancelacion').selectpicker('refresh');
  evaluarAbono();
}

//funcion mostrar formulario
function mostrarform(flag) {
  limpiar();
  if (flag) {
    $('#btnCreacionRapidaCliente').show();
    $('.ocultar-vista').show();
    $('#btnRealizarVenta').show();
    $('#detalles tbody').html('');
    $('#total').html('0');
    $('#listadoregistros').hide();
    $('#formularioregistros').show();
    //$("#btnGuardar").prop("disabled",false);
    $('#btnagregar').hide();
    $('#btnEntregar').hide();
    $('#btnDeuda').hide();

    datarticulos = [];
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
      iDisplayLength: 10, //paginacion
      order: [[1, 'desc']], //ordenar (columna, orden)
      columnDefs: [
        {
          targets: 0, // Índice de la columna que quieres que no sea ordenable (columna 1)
          orderable: false, // Desactiva el ordenamiento
        },
      ],
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
          d.condicion = 1;
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
  $('#tblarticulos tbody').off('click', '.btn-agregar-articulo'); // Quita manejadores previos
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
    var precio_venta = parseFloat(tr.find('td').eq(7).text());
    var imagen = datarticulos.find((item) => item.idarticulo == idarticulo).imagen;

    agregarDetalle(idarticulo, articulo, precio_venta, imagen);
  });
}
//funcion para guardaryeditar
function guardaryeditar(e) {
  e.preventDefault();
  let valid = true;
  let mensaje = '';

  var formData = new FormData($('#formulario')[0]);

  let subtotal = parseFloat($('#total_venta').val());
  let idtipocancelacion = parseInt(formData.get('tipocancelacion'));
  let abono = formData.get('abono') != '' ? parseFloat(formData.get('abono')) : null;

  // Validación: si es parcial, el abono debe ser menor al subtotal y mayor que 0
  if (idtipocancelacion == 2) {
    if (!abono || abono <= 0) {
      valid = false;
      mensaje = 'Debe ingresar un monto de adelanto mayor a 0 para cancelación parcial.';
    } else if (abono >= subtotal) {
      valid = false;
      mensaje = 'El adelanto no puede ser mayor o igual al total de la venta.';
      $('#abono').val(0); // resetea el campo
    }
  }

  if (!valid) {
    alert(mensaje);
    return;
  }

  let json = {
    idcliente: parseInt(formData.get('idcliente')),
    fechahora: formData.get('fecha_hora'),
    idtipocancelacion: parseInt(formData.get('tipocancelacion')),
    adelanto: formData.get('abono') != '' ? parseFloat(formData.get('abono')) : null,
    detalle: [],
  };

  let idsarticulos = formData.getAll('idarticulo[]');
  let idstallas = formData.getAll('idtalla[]');
  let cantidades = formData.getAll('cantidad[]');
  let preciosventa = formData.getAll('precio_venta[]');
  let descuentos = formData.getAll('descuento[]');

  idsarticulos.forEach((idarticulo, index) => {
    json.detalle.push({
      idtalla: parseInt(idstallas[index]),
      cantidad: parseInt(cantidades[index]),
      precioventa: parseFloat(preciosventa[index]),
      descuento: parseFloat(descuentos[index]),
      idarticulo: parseInt(idsarticulos[index]),
    });
  });

  if (!valid) {
    alert(mensaje);
    return;
  }

  $.ajax({
    url: '../ajax/venta.php?op=guardar',
    type: 'POST',
    data: JSON.stringify(json),
    contentType: 'application/json',
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

    $('#tipocancelacion').val(data.idtipo_cancelacion);
    evaluarAbono();
    $('#abono').val(data.adelanto);
    $('#abono').attr('disabled', 'true');
    $('#tipocancelacion').attr('disabled', 'true');
    $('#tipocancelacion').selectpicker('refresh');

    $('#btnCreacionRapidaCliente').hide();
    $('#btnRealizarVenta').hide();

    $('#idcliente').val(data.idcliente);
    $('#idcliente').attr('disabled', 'true');
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

    if (parseInt(data.estado) != 3) {
      if (parseInt(data.estado) == 1) {
        $('#btnEntregar').show();
      }

      if (parseInt(data.pagado) == 0) {
        $('#btnDeuda').show();
      } else {
        $('#colPendiente').hide();
      }
    }

    $.getJSON('../ajax/venta.php?op=listarDetalle&idventa=' + idventa, function (r) {
      $('#detalles tbody').html('');
      let total = 0;
      r.forEach((fila) => {
        let filaElement = `
          <tr>
            <td class="ocultar-vista"></td>
            <td><img src="../files/articulos/${fila.imagen}" width="50" height="50"></td>
            <td>${fila.nombre}</td>
            <td>${fila.talla}</td>
            <td class="ocultar-vista"></td>
            <td>${fila.cantidad}</td>
            <td>${fila.precioventa}</td>
            <td>${fila.descuento}</td>
            <td>${fila.subtotal}</td>
          </tr>
        `;
        $('#detalles tbody').append(filaElement);

        total += parseFloat(fila.subtotal);
      });

      $('#total').text(total);
      $('.ocultar-vista').hide();
      $('#saldo_pendiente').val(total - parseFloat(data.adelanto));
    });
  });
}

//funcion para desactivar
function anular(idventa) {
  bootbox.confirm('¿Está seguro de anular esta venta?', function (result) {
    if (result) {
      $.post('../ajax/venta.php?op=anular', { idventa: idventa }, function (e) {
        bootbox.alert(e);
        tabla.ajax.reload(); // Recarga la lista de ventas
      });
    }
  });
}

function Entregar(idventa) {
  bootbox.confirm('la entrega se completará, por favor confirmar.', function (result) {
    if (result) {
      $.post('../ajax/venta.php?op=entregar', { idventa: idventa }, function (e) {
        bootbox.alert(e);
        tabla.ajax.reload();
        mostrarform(false);
      });
    }
  });
}

function CompletarPago(idventa) {
  bootbox.confirm('La venta se marcará como pagado, por favor confirmar.', function (result) {
    if (result) {
      $.post('../ajax/venta.php?op=completarpago', { idventa: idventa }, function (e) {
        bootbox.alert(e);
        tabla.ajax.reload();
        mostrarform(false);
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
      '" min="0" onchange="actualizarSubtotal(\'' +
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

  // Si el campo cantidad está vacío o no es número, deja el subtotal en 0
  if (cantidadInput.val() === '' || isNaN(cantidad) || cantidad < 1) {
    fila.find('span[name="subtotal"]').text('0');
    modificarSubtotales();
    verificarBotonVenta();
    return;
  }

  // Si la cantidad supera el stock, la cantidad se vuelve 1
  if (cantidad > stock) {
    cantidad = 1;
    cantidadInput.val(1);
    alert('No puedes ingresar una cantidad mayor al stock disponible. Se estableció en 1.');
  }

  var subtotalBase = cantidad * precio;

  // Validar descuento: no mayor que subtotal, ni negativo
  if (descuento > subtotalBase) {
    descuento = 0;
    descuentoInput.val(0);
    alert('El descuento no puede ser mayor que el subtotal. Se estableció en 0.');
  } else if (descuento < 0) {
    descuento = 0;
    descuentoInput.val(0);
    alert('El descuento no puede ser negativo. Se estableció en 0.');
  }

  var subtotal = subtotalBase - descuento;
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
  validarAbonoContraSubtotal();
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

// Siempre define la función:
function validarAbonoContraSubtotal() {
  let subtotal = parseFloat($('#total_venta').val()) || 0;
  let abono = parseFloat($('#abono').val()) || 0;
  if (abono > subtotal) {
    alert('El adelanto no puede ser mayor que el total de la venta.');
    $('#abono').val(0);
    abono = 0;
  }
  let pendiente = subtotal - abono;
  $('#saldo_pendiente').val(pendiente.toFixed(2));
}

$(document).ready(function () {
  $('#abono').on('input', validarAbonoContraSubtotal);
});

// Pero SIEMPRE llama a validarAbonoContraSubtotal() después de actualizar el subtotal con JS
// Ejemplo:
$('#descuento').on('input', function () {
  // ... tu lógica para actualizar el subtotal ...
  $('#total_venta').val(nuevoSubtotal);
  validarAbonoContraSubtotal(); // <-- ¡Esto es lo importante!
});
$('#detalles').on('input', '.cantidad, .descuento', function () {
  var fila = $(this).closest('tr');
  var filaId = fila.attr('id');
  actualizarSubtotal(filaId);
});

init();
