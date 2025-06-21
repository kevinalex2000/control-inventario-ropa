<?php
//activamos almacenamiento en el buffer
ob_start();
session_start();
if (!isset($_SESSION['nombre'])) {
  header("Location: login.html");
} else {


  require 'header.php';

  if ($_SESSION['ventas'] == 1) {

    ?>
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">

        <!-- Default box -->
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">
                  Ventas
                  <button class="btn btn-success btn-sm" onclick="mostrarform(true)" id="btnagregar"><i
                      class="fa fa-plus-circle"></i> Nuevo</button>
                </h1>
                <div class="box-tools pull-right">

                </div>
              </div>
              <!--box-header-->
              <!--centro-->
              <div class="panel-body table-responsive" id="listadoregistros">
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                  <thead>
                    <th>Opciones</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Usuario</th>
                    <th>Total Venta</th>
                    <th>Estado de pago</th>
                    <th>Estado</th>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    <th>Opciones</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Usuario</th>
                    <th>Total Venta</th>
                    <th>Estado de pago</th>
                    <th>Estado</th>
                  </tfoot>
                </table>
              </div>
              <div class="panel-body" id="formularioregistros">
                <form action="" name="formulario" id="formulario" method="POST">
                  <div class="form-group col-lg-8 col-md-8 col-xs-12">
                    <label for="">Cliente:</label>
                    <input class="form-control" type="hidden" name="idventa" id="idventa">
                    <div class="input-group">
                      <select name="idcliente" id="idcliente" class="form-control selectpicker" data-live-search="true"
                        required>
                        <option value="">--Seleccione--</option>
                      </select>
                      <span class="input-group-btn">
                        <a data-toggle="modal" href="#modalCliente">

                          <button class="btn btn-default" style="margin-left: 10px;">
                            <i class="fa fa-user-plus"></i> Creación rapida
                          </button>
                        </a>
                      </span>

                    </div>
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-xs-12">
                    <label for="">Fecha(*): </label>
                    <input class="form-control" type="date" name="fecha_hora" id="fecha_hora" required readonly>
                  </div>
                  <!--
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Tipo Comprobante(*): </label>
                    <select name="tipo_comprobante" id="tipo_comprobante" class="form-control selectpicker" required>
                      <option value="1">Nota de venta</option>
                    </select>
              </div>
      -->
                  <!--
                  <div class="form-group col-lg-2 col-md-2 col-xs-6">
                    <label for="">Serie: </label>
                    <input class="form-control" type="text" name="serie_comprobante" id="serie_comprobante" maxlength="7"
                      placeholder="Serie">
                  </div>
                  <div class="form-group col-lg-2 col-md-2 col-xs-6">
                    <label for="">Número: </label>
                    <input class="form-control" type="text" name="num_comprobante" id="num_comprobante" maxlength="10"
                      placeholder="Número" required>
                  </div>
                  <div class="form-group col-lg-2 col-md-2 col-xs-6">
                    <label for="">Impuesto: </label>
                    <input class="form-control" type="text" name="impuesto" id="impuesto">
                  </div>
  -->
                  <div class="form-group col-md-12">

                    <a data-toggle="modal" href="#myModal">
                      <button id="btnAgregarArt" type="button" class="btn btn-default"><i class="fa fa-plus"></i> Agregar
                        articulos</button>
                    </a>
                  </div>
                  <div class="col-lg-12 col-md-12 col-xs-12">
                    <table id="detalles" class="table table-striped table-bordered table-condensed table-hover  head-black">
                      <thead style="background-color:#A9D0F5">
                        <th>Opciones</th>
                        <th>Imagen</th>
                        <th>Articulo</th>
                        <th>Talla</th>
                        <th>Stock Actual</th>
                        <th>Cantidad</th>
                        <th>Precio Venta</th>
                        <th>Descuento</th>
                        <th>Subtotal</th>
                      </thead>
                      <tbody>
                      </tbody>
                      <tfoot>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>Total (S/)</th>
                        <th>
                          <div id="total">S/. 0.00</div><input type="hidden" name="total_venta" id="total_venta">
                        </th>
                      </tfoot>
                    </table>
                  </div>


                  <div class="form-group col-md-3 form-inline">
                    <label for="">Cancelación: </label>
                    <select name="tipocancelacion" id="tipocancelacion" class="form-control selectpicker" required
                      onchange="evaluarAbono()">
                      <option value="1">Total</option>
                      <option value="2">Parcial</option>
                    </select>
                  </div>
                  <div class="col-md-3" id="colAbono" style="display: none">
                    <label for="">Abono(*): </label>
                    <input name="abono" id="abono" class="form-control" type="number" placeholder="-">
                  </div>
                  <div class="col-md-3">
                    <label for="">Pendiente por pagar:</label>
                    <input name="saldo_pendiente" id="saldo_pendiente" class="form-control" type="text" readonly>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:25px;">
                    <button class="btn btn-primary" type="submit" id="btnRealizarVenta" disabled><i class="fa fa-money"></i>
                      Realizar Venta</button>
                    <button class="btn btn-danger" onclick="cancelarform()" type="button" id="btnCancelar"><i
                        class="fa fa-arrow-circle-left"></i> Cancelar</button>
                  </div>
                </form>
              </div>
              <!--fin centro-->
            </div>
          </div>
        </div>
        <!-- /.box -->

      </section>
      <!-- /.content -->
    </div>

    <!--Modal-->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width: 65% !important;">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Seleccione un Articulo</h4>
          </div>
          <div class="modal-body">
            <div class="row" style="margin-bottom: 30px;">
              <!-- Filtro Categoría -->
              <div class="col-md-3">
                <label for="filtroCategoria">Categoría</label>
                <select id="filtroCategoria" class="form-control input-sm ">
                  <option value="">Todas</option>
                </select>
              </div>

              <!-- Filtro Talla -->
              <div class="col-md-3">
                <label for="filtroTalla">Talla</label>
                <select id="filtroTalla" class="form-control input-sm">
                  <option value="">Todas</option>
                </select>
              </div>

              <!-- Botón Excel -->
              <div class="col-md-2" style="padding-top: 25px;">
                <button id="btnExportExcel" class="btn btn-sm" onclick="listarArticulos()">
                  <span class="fa fa-filter"></span> Filtrar
                </button>
              </div>

              <!-- Campo Buscar -->
              <div class="col-md-4">
                <label for="buscarTabla">Buscar</label>
                <input type="text" id="buscarTabla" class="form-control input-sm" placeholder="Buscar en la tabla..."
                  onkeyup="buscarEnTabla(this.value, tablaarticulos)">
              </div>
            </div>

            <table id="tblarticulos" class="table table-striped table-bordered table-condensed table-hover"
              style="width: 100%">
              <thead>
                <th></th>
                <th>Talla</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Categoria</th>
                <th>Código</th>
                <th>Stock <span class="tallafiltrada"></span></th>
                <th>Precio</th>
              </thead>
              <tbody>

              </tbody>
              <tfoot>
                <th></th>
                <th>Talla</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Categoria</th>
                <th>Código</th>
                <th>Stock <span class="tallafiltrada"></span></th>
                <th>Precio</th>
              </tfoot>
            </table>
          </div>
          <div class="modal-footer">
            <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- fin Modal-->

    <!---->
    <div class="modal fade" id="modalCliente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="formCreacionRapidaCliente" method="POST">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title">Creación rápida de cliente</h4>
            </div>
            <div class="modal-body">
              <div class="row">
                <input type="hidden" name="tipo_persona" class="form-control" value="Cliente">
                <!-- Filtro Categoría -->
                <div class="col-md-12 form-group">
                  <label for="filtroCategoria">Nombre:</label>
                  <input type="text" placeholder="Nombre del cliente" name="nombre" class="form-control">
                </div>
                <!-- Filtro Talla -->
                <div class="col-md-12 form-group">
                  <label for="filtroTalla">Teléfono(*):</label>
                  <input type="phone" placeholder="Número de Telefono" name="telefono" id="telefonoCreacionRapida"
                    class="form-control" maxlength="20" required>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" type="submit">Crear</button>
              <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- fin Modal-->
    <?php
  } else {
    require 'noacceso.php';
  }

  require 'footer.php';
  ?>
  <script src="scripts/venta.js"></script>
  <?php
}

ob_end_flush();
?>