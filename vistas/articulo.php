<?php
//activamos almacenamiento en el buffer
ob_start();
session_start();
if (!isset($_SESSION['nombre'])) {
  header("Location: login.html");
} else {

  require 'header.php';
  if ($_SESSION['almacen'] == 1) {
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
                  Articulos
                  <button class="btn btn-success btn-sm" onclick="mostrarform(true)" id="btnagregar"><i
                      class="fa fa-plus-circle"></i> Nuevo</button>
                  <!--<a target="_blank" href="../reportes/rptarticulos.php"><button class="btn btn-info">Reporte</button></a>-->
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
                    <label for="filtroCategoria">Categoría</label>
                    <select id="filtroCategoria" class="form-control input-sm ">
                      <option value="">Todas</option>
                    </select>
                  </div>

                  <!-- Filtro Talla -->
                  <div class="col-md-2">
                    <label for="filtroTalla">Talla</label>
                    <select id="filtroTalla" class="form-control input-sm">
                      <option value="">Todas</option>
                    </select>
                  </div>

                  <!-- Filtro Estado -->
                  <div class="col-md-2">
                    <label for="filtroEstado">Estado</label>
                    <select id="filtroEstado" class="form-control input-sm">
                      <option value="">Todos</option>
                      <option value="1">Activo</option>
                      <option value="0">Inactivo</option>
                    </select>
                  </div>

                  <!-- Botón Excel -->
                  <div class="col-md-2" style="padding-top: 25px;">
                    <button id="btnExportExcel" class="btn btn-sm" onclick="listar()">
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
                <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                  <thead>
                    <th>Opciones</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoria</th>
                    <th>Codigo</th>
                    <th>Stock <span class="tallafiltrada"> </span></th>
                    <th>Descripcion</th>
                    <th>Precio</th>
                    <th>Estado</th>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    <th>Opciones</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoria</th>
                    <th>Codigo</th>
                    <th>Stock <span class="tallafiltrada"> </span></th>
                    <th>Descripcion</th>
                    <th>Precio</th>
                    <th>Estado</th>
                  </tfoot>
                </table>
              </div>
              <div class="panel-body" id="formularioregistros">
                <form action="" name="formulario" id="formulario" method="POST">
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Nombre(*):</label>
                    <input class="form-control" type="hidden" name="idarticulo" id="idarticulo">
                    <input class="form-control" type="text" name="nombre" id="nombre" maxlength="100" placeholder="Nombre"
                      required>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Codigo(*):</label>
                    <input class="form-control" type="text" name="codigo" id="codigo" placeholder="codigo del prodcuto"
                      required>
                    <!-- <button class="btn btn-success" type="button" onclick="generarbarcode()">Generar</button>
      <button class="btn btn-info" type="button" onclick="imprimir()">Imprimir</button>
      <div id="print">
        <svg id="barcode"></svg>
      </div> -->
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Categoria:</label>
                    <select name="idcategoria" id="idcategoria" class="form-control selectpicker"></select>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Descripcion</label>
                    <input class="form-control" type="text" name="descripcion" id="descripcion" maxlength="256"
                      placeholder="Descripcion">
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Stock por tallas:</label>
                    <table id="idtblstockxtallas" class="table table-bordered table-condensed text-center"
                      style="max-width: 300px">
                      <thead>
                        <tr class="active">
                          <th class="text-center">Talla</th>
                          <th class="text-center">Stock</th>
                        </tr>
                      </thead>
                      <tbody>
                      </tbody>
                    </table>
                    <!--<input class="form-control" type="number" name="stock" id="stock"  required> -->
                  </div>

                  <div class="form-group col-lg-6 col-md-6 col-xs-12">
                    <label for="">Precio de Venta(*):</label>
                    <input name="precio_venta" id="precio_venta" type="number" class="form-control" min="1" step="0.01"
                      placeholder="0.00" required />


                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-xs-12">

                    <label for="">Imagen(*):</label>
                    <input class="form-control" type="file" name="imagen" id="imagen" accept="image/*" required>
                    <input type="hidden" name="imagenactual" id="imagenactual">
                    <img style="padding-top:10px;" width="150px" height="120" id="imagenmuestra">

                  </div>
                  <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i>
                      Guardar</button>

                    <button class="btn btn-danger" onclick="cancelarform()" type="button"><i
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
    <?php
  } else {
    require 'noacceso.php';
  }
  require 'footer.php'
    ?>
  <script src="../public/js/JsBarcode.all.min.js"></script>
  <script src="../public/js/jquery.PrintArea.js"></script>
  <script src="scripts/articulo.js"></script>

  <?php
}

ob_end_flush();
?>