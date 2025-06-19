var listaTallas = [];
var tabla;
var tablaArticulos;

var impuesto = 18;
var cont = 0;
var cantFilasDetalles = 0;

//funcion que se ejecuta al inicio
function init() {
  mostrarform(false);
  listar();

  $('#formulario').on('submit', function (e) {
    guardaryeditar(e);
  });

  //cargamos los items al select proveedor
  $.getJSON('../ajax/persona.php?op=listar&idtipopersona=2', function (r) {
    const selectProveedor = $('#idproveedor');
    const filtroproveedor = $('#filtroProveedor');

    r.forEach((proveedor) => {
      const numdoc =
        proveedor.numdocumento !== ''
          ? proveedor.tipodocumento + ': ' + proveedor.numdocumento
          : 'Sin doc';
      const texto = (numdoc + ' - ' + proveedor.nombre).toUpperCase();
      const option = `<option value="${proveedor.idpersona}">${texto}</option>`;

      filtroproveedor.append(option);
      selectProveedor.append(option);
    });

    $('#idproveedor').selectpicker('refresh');
  });
}

//funcion limpiar
function limpiar() {
  $('#idproveedor').val('');
  $('#proveedor').val('');
  $('#serie_comprobante').val('');
  $('#num_comprobante').val('');
  $('#impuesto').val('');

  $('#total_compra').val('');
  $('#detalles tbody').html('');
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
  $('#idproveedor').selectpicker('refresh');
}

//funcion mostrar formulario
function mostrarform(flag) {
  limpiar();
  if (flag) {
    $('#listadoregistros').hide();
    $('#formularioregistros').show();
    $('#btnagregar').hide();
    listarArticulos();

    $('#btnGuardar').prop('disabled', true);
    $('#btnGuardar').show();
    $('#btnCancelar').show();
    cantFilasDetalles = 0;
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
        url: '../ajax/ingreso.php?op=listar',
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

function cargarTallas(callback) {
  $.getJSON('../ajax/talla.php', function (data) {
    listaTallas = data;
    if (typeof callback === 'function') callback();
  });
}

function listarArticulos() {
  cargarTallas(function () {
    tablaArticulos = $('#tblarticulos').DataTable({
      destroy: true,
      aProcessing: true,
      aServerSide: true,
      ajax: {
        url: '../ajax/ingreso.php?op=listarArticulos',
        type: 'get',
        dataType: 'json',
      },
      columns: [
        { data: 0, orderable: false }, // Botón opciones
        {
          data: null,
          render: function (data, type, row, meta) {
            // data = toda la fila como array u objeto según tu backend
            var idarticulo = data[6]; // <-- aquí obtienes el código del artículo
            // Si necesitas usarlo (por ejemplo, en el select):
            var select =
              '<select class="form-control input-sm select-talla" style="width: 80px;" data-idarticulo="' +
              idarticulo +
              '">';
            select += '<option value="">-</option>';
            for (var i = 0; i < listaTallas.length; i++) {
              select +=
                '<option value="' +
                listaTallas[i].idtalla +
                '">' +
                listaTallas[i].nombre +
                '</option>';
            }
            select += '</select>';
            return select;
          },
        },
        { data: 1 }, // Nombre
        { data: 2 }, // Categoria
        { data: 3 }, // Código
        { data: 4 }, // Stock
        { data: 5 }, // Imagen
        { data: 6, visible: false }, //idarticulo
      ],
      iDisplayLength: 10,
      lengthChange: false,
    });
  });
}
//funcion para guardaryeditar
function guardaryeditar(e) {
  e.preventDefault();
  let valid = true;
  let mensaje = '';

  var formData = new FormData($('#formulario')[0]);

  let json = {
    idproveedor: formData.get('idproveedor'),
    fechahora: formData.get('fecha_hora'),
    detalle: [],
  };

  let idsarticulos = formData.getAll('idarticulo[]');
  let idstallas = formData.getAll('idtalla[]');
  let cantidades = formData.getAll('cantidad[]');
  let precioscompra = formData.getAll('precio_compra[]');

  idsarticulos.forEach((idarticulo, index) => {
    json.detalle.push({
      idtalla: idstallas[index],
      cantidad: cantidades[index],
      preciocompra: precioscompra[index],
      idarticulo: idsarticulos[index],
    });

    if (precioscompra[index] == '' || precioscompra[index] < 0) {
      mensaje =
        'El precio de compra del producto nro [' +
        (index + 1) +
        '] tiene un valor invalido o no tiene valor.';
      valid = false;
    }
  });

  if (!valid) {
    alert(mensaje);
    return;
  }

  $.ajax({
    url: '../ajax/ingreso.php?op=guardar',
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

function mostrar(idingreso) {
  mostrarform(true);

  $.post(
    '../ajax/ingreso.php?op=mostrar',
    {
      idingreso: idingreso,
    },
    function (data, status) {
      data = JSON.parse(data);
      $('#idproveedor').val(data.idproveedor);
      $('#idproveedor').selectpicker('refresh');
      $('#tipo_comprobante').val(data.tipo_comprobante);
      $('#tipo_comprobante').selectpicker('refresh');
      $('#serie_comprobante').val(data.serie_comprobante);
      $('#num_comprobante').val(data.num_comprobante);
      $('#fecha_hora').val(data.fecha);
      $('#impuesto').val(data.impuesto);
      $('#idingreso').val(data.idingreso);

      //ocultar y mostrar los botones
      $('#btnGuardar').hide();
      $('#btnCancelar').show();
      $('#btnAgregarArt').hide();
    }
  );

  console.log(idingreso);
  $.getJSON('../ajax/ingreso.php?op=listarDetalle&id=' + idingreso, function (data) {
    $('#detalles tbody').html('');
    let total = 0;
    data.forEach(function (item, index) {
      tr = `
      <tr class="filas">
        <td></td>
        <td> <img src= "../files/articulos/${item.imagen}" width="50px" height="50px"></td>
        <td>${item.articulo}</td>
        <td>${item.talla}</td>
        <td>${item.cantidad}</td>
        <td>${item.preciocompra}</td>
        <td>${item.subtotal}</td>
      </tr>`;
      $('#detalles tbody').append(tr);
      total += parseFloat(item.subtotal);
    });

    $('#total').text(total);
  });
}

//funcion para desactivar
function anular(idingreso) {
  bootbox.confirm('¿Esta seguro de desactivar este dato?', function (result) {
    if (result) {
      $.post('../ajax/ingreso.php?op=anular', { idingreso: idingreso }, function (e) {
        bootbox.alert(e);
        tabla.ajax.reload();
      });
    }
  });
}

$('#tipo_comprobante').change(marcarImpuesto);

function marcarImpuesto() {
  var tipo_comprobante = $('#tipo_comprobante option:selected').text();
  if (tipo_comprobante == 'Factura') {
    $('#impuesto').val(impuesto);
  } else {
    $('#impuesto').val('0');
  }
}

function agregarDetalle(idarticulo, articulo, imagen, precio_venta) {
  var selectTalla = $('.select-talla[data-idarticulo="' + idarticulo + '"]');
  var idtalla = selectTalla.val();
  var nombreTalla = selectTalla.find('option:selected').text();

  if (idtalla == '') {
    alert('Debe seleccionar una talla para el producto');
    return;
  }

  // Buscar si ya existe una fila con este artículo y talla
  var existe = false;
  $('#detalles tbody tr').each(function () {
    var $tr = $(this);
    var articuloFila = $tr.find('input[name="idarticulo[]"]').val();
    var tallaFila = $tr.find('input[name="idtalla[]"]').val();

    if (articuloFila == idarticulo && tallaFila == idtalla) {
      // Si existe, suma 1 a la cantidad
      var $cantidadInput = $tr.find('input.cantidad');
      var nuevaCantidad = parseInt($cantidadInput.val() || 0) + 1;
      $cantidadInput.val(nuevaCantidad);

      // Forzar el evento para recalcular subtotal y total
      $cantidadInput.trigger('input');
      existe = true;
      return false; // salir del each
    }
  });

  if (existe) return; // No agregar nueva fila si ya se sumó

  var cantidad = 1;
  var precio_compra = '';

  if (idarticulo != '') {
    var subtotal = 0;
    var fila =
      '<tr id="fila' +
      cont +
      '">' +
      '<td><button type="button" class="btn btn-danger" onclick="eliminarDetalle(' +
      cont +
      ')">X</button></td>' +
      '<td><img src="../files/articulos/' +
      imagen +
      '" width="50" height = "50"></td>' +
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
      '<td><input type="number" name="cantidad[]" class="cantidad" value="' +
      cantidad +
      '" min="1" style="width:70px;"></td>' +
      '<td><input type="number" name="precio_compra[]" class="precio-compra" value="' +
      precio_compra +
      '" min="0" step="0.01" placeholder="0.00" style="width:90px;"></td>' +
      /*+
      '<td><input type="text" name="precio_venta[]" class="precio-venta" value="' +
      precio_venta +
      '" readonly style="width:90px;background:#eee;"></td>' */
      '<td><span id="subtotal' +
      cont +
      '" name="subtotal" class="subtotal">0.00</span></td>' +
      '</tr>';
    cont++;
    cantFilasDetalles++;

    $('#detalles tbody').append(fila);
    recalcularEventosDetalle();
    evaluar();
  } else {
    alert('error al ingresar el detalle, revisar las datos del articulo ');
  }
}

function recalcularEventosDetalle() {
  // Cálculo automático de subtotal y total

  $('#detalles').off('input', '.cantidad, .precio-compra');
  $('#detalles').on('input', '.cantidad, .precio-compra', function () {
    var $tr = $(this).closest('tr');
    var cantidad = parseFloat($tr.find('.cantidad').val()) || 0;
    var precioCompra = parseFloat($tr.find('.precio-compra').val()) || 0;
    if (precioCompra < 0) precioCompra = 0;
    var subtotal = (cantidad * precioCompra).toFixed(2);
    $tr.find('.subtotal').text(subtotal);
    recalcularTotales();
  });

  $('#detalles').off('click', '.btn-danger');
  $('#detalles').on('click', '.btn-danger', function () {
    $(this).closest('tr').remove();
    recalcularTotales();
    detalles--;
    evaluar();
  });
}

function recalcularTotales() {
  var total = 0;
  $('.subtotal').each(function () {
    total += parseFloat($(this).text()) || 0;
  });
  $('#total').html(total.toFixed(2));
  $('#total_compra').val(total.toFixed(2));
  evaluar();
}

function evaluar() {
  if (cantFilasDetalles > 0) {
    $('#btnGuardar').prop('disabled', false);
  } else {
    $('#btnGuardar').prop('disabled', true);
    cont = 0;
  }
}

function modificarSubtotales() {
  var cant = document.getElementsByName('cantidad[]');
  var prec = document.getElementsByName('precio_compra[]');
  var sub = document.getElementsByName('subtotal');

  for (var i = 0; i < cant.length; i++) {
    var inpC = cant[i];
    var inpP = prec[i];
    var inpS = sub[i];

    inpS.value = inpC.value * inpP.value;
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
  $('#total_compra').val(total);
}

function eliminarDetalle(indice) {
  $('#fila' + indice).remove();
  calcularTotales();
  cantFilasDetalles -= 1;
  evaluar();
}

init();
