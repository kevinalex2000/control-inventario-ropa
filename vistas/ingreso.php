<?php
//activamos almacenamiento en el buffer
ob_start();
session_start();
if (!isset($_SESSION['nombre'])) {
  header("Location: login.html");
} else {


  require 'header.php';

  if ($_SESSION['compras'] == 1) {

    ?>
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">

        <!-- Default box -->
        <div class="row">
          <div class="col-md-12">
            <div class="box">
              <div class="box-header with-border">
                <h1 class="box-title">Ingresos

                  <button class="btn btn-success btn-sm" onclick="mostrarform(true)" id="btnagregar"><i
                      class="fa fa-plus-circle"></i> Nuevo</button>
                </h1>
                <div class="box-tools pull-right">

                </div>
              </div>
              <!--box-header-->
              <!--centro-->
              <div class="panel-body table-responsive" id="listadoregistros">

                <div class="row" style="margin-bottom: 30px;">
                  <!-- Filtro Categoría -->
                  <div class="col-md-2">
                    <label for="filtroCategoria">Desde</label>
                    <input class="form-control" type="date" name="fecha_desde" id="fecha_desde" required>
                  </div>

                  <!-- Filtro Talla -->
                  <div class="col-md-2">
                    <label for="filtroTalla">Hasta</label>
                    <input class="form-control" type="date" name="fecha_hasta" id="fecha_hasta" required>
                  </div>

                  <!-- Filtro Estado -->
                  <div class="col-md-2">
                    <label for="filtroProveedor">Proveedor</label>
                    <select id="filtroProveedor" class="form-control input-sm">
                      <option value="">Todos</option>
                    </select>
                  </div>

                  <!-- Botón Excel -->
                  <div class="col-md-2" style="padding-top: 25px;">
                    <button id="btnExportExcel" class="btn btn-sm" onclick="listarConFiltro()">
                      <span class="fa fa-filter"></span> Filtrar
                    </button>
                  </div>

                  <!-- Campo Buscar -->
                  <div class="col-md-4">
                    <label for="buscarTabla">Buscar</label>
                    <input type="text" id="buscarTabla" class="form-control input-sm" placeholder="Buscar en la tabla..."
                      onkeyup="buscarEnTabla(this.value, tabla)">
                  </div>
                </div>

                <div class="row" style="margin-bottom: 10px;">
                  <div class="col-md-12">
                    <button id="btnExportExcel" class="btn btn-sm btn-default" onclick="ExportarExcelDeTabla()">
                      <span class="fa fa-download"></span> Exportar en excel
                    </button>
                  </div>
                </div>
                <div class="overflow-auto">
                  <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                    <thead>
                      <th>Opciones</th>
                      <th>Fecha</th>
                      <th>Proveedor</th>
                      <th>Total Compra</th>
                      <th>Usuario</th>
                      <th>Estado</th>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                      <th>Opciones</th>
                      <th>Fecha</th>
                      <th>Proveedor</th>
                      <th>Total Compra</th>
                      <th>Usuario</th>
                      <th>Estado</th>
                    </tfoot>
                  </table>

                </div>
              </div>
              <div class="panel-body" id="formularioregistros">
                <form action="" name="formulario" id="formulario" method="POST">
                  <div class="form-group col-lg-8 col-md-8 col-xs-12">
                    <label for="">Proveedor(*):</label>
                    <input class="form-control" type="hidden" name="idingreso" id="idingreso">
                    <select name="idproveedor" id="idproveedor" class="form-control selectpicker" data-live-search="true"
                      required>
                      <option value="">--Seleccione--</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-xs-12">
                    <label for="">Fecha(*): </label>
                    <input class="form-control" type="date" name="fecha_hora" id="fecha_hora" readonly>
                  </div>
                  <!--
     <div class="form-group col-lg-6 col-md-6 col-xs-12">
      <label for="">Tipo Comprobante(*): </label>
     <select name="tipo_comprobante" id="tipo_comprobante" class="form-control selectpicker" required>
       <option value="Boleta">Nota de venta</option>
       <option value="Factura">Factura</option>
       <option value="Ticket">Ticket</option>
     </select>
    </div>
     <div class="form-group col-lg-2 col-md-2 col-xs-6">
      <label for="">Serie: </label>
      <input class="form-control" type="text" name="serie_comprobante" id="serie_comprobante" maxlength="7" placeholder="Serie">
    </div>
     <div class="form-group col-lg-2 col-md-2 col-xs-6">
      <label for="">Número: </label>
      <input class="form-control" type="text" name="num_comprobante" id="num_comprobante" maxlength="10" placeholder="Número" required>
    </div>
    <div class="form-group col-lg-2 col-md-2 col-xs-6">
      <label for="">Impuesto: </label>
      <input class="form-control" type="text" name="impuesto" id="impuesto">
    </div>
-->
                  <div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a data-toggle="modal" href="#myModal">
                      <button id="btnAgregarArt" type="button" class="btn btn-default"><i class="fa fa-plus"></i> Agregar
                        articulos</button>
                    </a>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-xs-12" style="z-index: 100">
                    <div class="overflow-auto">
                      <table id="detalles"
                        class="table table-striped table-bordered table-condensed table-hover head-black">
                        <thead>
                          <th>Opciones</th>
                          <th>Imagen</th>
                          <th>Articulo</th>
                          <th>Talla</th>
                          <th>Cantidad</th>
                          <th>Precio Compra</th>
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
                          <th>Total (S/)</th>
                          <th>
                            <div id="total">S/. 0.00</div><input type="hidden" name="total_compra" id="total_compra">
                          </th>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i>
                      Guardar</button>
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
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Seleccione un Articulo</h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <!-- Campo Buscar -->
              <div class="col-md-12 form-group form-inline" style="text-align:right;">
                <label for="buscarTabla">Buscar:</label>
                <input type="text" id="buscarTabla" class="form-control input-sm" placeholder="Buscar en la tabla..."
                  onkeyup="buscarEnTabla(this.value, tablaArticulos)">
              </div>
            </div>
            <div class="overflow-auto">
              <table id="tblarticulos" class="table table-striped table-bordered table-condensed table-hover dataTable"
                style="width:100%">
                <thead>
                  <th></th>
                  <th>Talla</th>
                  <th>Imagen</th>
                  <th>Nombre</th>
                  <th>Categoria</th>
                  <th>Código</th>
                  <th>Stock</th>
                  <th>ID Artículo</th>
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
                  <th>Stock</th>
                  <th>ID Artículo</th>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
          </div>
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
  <script src="scripts/ingreso.js"></script>
  <?php
}

ob_end_flush();
?>